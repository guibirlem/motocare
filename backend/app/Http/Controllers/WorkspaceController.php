<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Despesa;
use App\Models\Manutencao;
use App\Models\User;
use App\Rules\NewPassword;
use App\Services\FuelStationMemory;
use App\Services\MotorcycleInsights;
use App\Services\PushEndpoint;
use App\Services\WebPushConfiguration;
use App\Services\WebPushService;
use App\Support\Money;
use App\Support\MotorcyclePlate;
use App\Support\MotorcycleServices;
use App\Support\TankConfiguration;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class WorkspaceController extends Controller
{
    private function moto(Request $request, string $moto)
    {
        return $request->user()->motocicletas()->findOrFail($moto);
    }

    public function updateMotorcycle(Request $request, string $moto)
    {
        $record = $this->moto($request, $moto);
        MotorcyclePlate::normalize($request);
        $data = $request->validate([
            'marca' => ['required', 'string', 'max:255'], 'modelo' => ['required', 'string', 'max:255'],
            'ano' => ['required', 'integer', 'between:1900,2100'],
            'placa' => ['required', 'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', Rule::unique('motocicletas', 'placa')->ignore($record->id)],
            'km_atual' => ['required', 'integer', 'between:0,4294967295'],
            ...TankConfiguration::rules(),
        ]);

        return DB::transaction(function () use ($request, $moto, $data) {
            $record = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            TankConfiguration::check($data, $record);
            // Valida o tanque na mesma versão bloqueada da moto e preserva o odômetro monotônico.
            $updated = $request->user()->motocicletas()->whereKey($record->id)->where('km_atual', '<=', $data['km_atual'])->update($data);
            if (! $updated && $record->km_atual > $data['km_atual']) {
                throw ValidationException::withMessages(['km_atual' => 'A quilometragem não pode diminuir. Atualize a página e confira o odômetro.']);
            }

            return response()->json($record->refresh());
        }, 3);
    }

    public function deleteMotorcycle(Request $request, string $moto)
    {
        $this->moto($request, $moto);
        DB::transaction(function () use ($request, $moto) {
            $record = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            $stations = $record->despesas()->whereNotNull('posto_id')->pluck('posto_id')->unique()->sort();
            $record->delete();
            foreach ($stations as $id) {
                $station = $request->user()->postos()->lockForUpdate()->find($id);
                if ($station) {
                    app(FuelStationMemory::class)->refresh($station);
                }
            }
        }, 3);

        return response()->noContent();
    }

    private function expenseData(Request $request): array
    {
        $data = $request->validate([
            'tipo_despesa' => ['required', Rule::in(['combustivel', 'reparo', 'documentacao', 'impostos', 'outros'])],
            'descricao' => ['nullable', 'required_if:tipo_despesa,reparo', 'string', 'max:5000'],
            'valor' => ['required', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
            'litros' => ['nullable', 'required_if:tipo_despesa,combustivel', 'numeric', 'decimal:0,2', 'between:0.01,999999.99'],
            'preco_litro' => ['nullable', 'required_if:tipo_despesa,combustivel', 'numeric', 'decimal:0,2', 'between:0.01,99999999.99'],
            'posto' => ['nullable', 'string', 'max:255'], 'data' => ['required', 'date_format:Y-m-d'],
            'posto_id' => ['nullable', 'integer', 'min:1'],
            'combustivel' => ['nullable', Rule::in(FuelStationMemory::FUELS)],
            'km_abastecimento' => ['nullable', 'integer', 'between:0,4294967295'],
            'tanque_cheio' => ['nullable', 'boolean'],
        ]);
        if ($data['tipo_despesa'] === 'combustivel') {
            if (isset($data['posto_id'])) {
                $station = $request->user()->postos()->findOrFail($data['posto_id']);
                $data['posto'] = $station->nome;
                if (empty($data['combustivel'])) {
                    throw ValidationException::withMessages(['combustivel' => 'Selecione o combustível do posto.']);
                }
            }
            if (isset($data['km_abastecimento']) && $data['data'] > today()->toDateString()) {
                throw ValidationException::withMessages(['data' => 'Um abastecimento com odômetro não pode estar no futuro.']);
            }
            if (($data['tanque_cheio'] ?? false) && ! isset($data['km_abastecimento'])) {
                throw ValidationException::withMessages(['km_abastecimento' => 'Informe o odômetro para usar tanque cheio no consumo.']);
            }
            $expected = intdiv(Money::cents($data['litros']) * Money::cents($data['preco_litro']) + 50, 100);
            if (abs(Money::cents($data['valor']) - $expected) > 1) {
                throw ValidationException::withMessages(['valor' => 'O total deve corresponder aos litros multiplicados pelo preço por litro.']);
            }
        } else {
            $data['litros'] = null;
            $data['preco_litro'] = null;
            $data['posto'] = null;
            $data['posto_id'] = null;
            $data['combustivel'] = null;
            $data['km_abastecimento'] = null;
            $data['tanque_cheio'] = null;
        }

        $data['descricao'] = $data['descricao'] ?? null;
        $data['posto'] = $data['posto'] ?? null;

        return $data + ['posto_id' => null, 'combustivel' => null, 'km_abastecimento' => null, 'tanque_cheio' => null];
    }

    public function expenses(Request $request, string $moto)
    {
        $query = $this->moto($request, $moto)->despesas();
        $data = $request->validate(['mes' => ['nullable', 'date_format:Y-m']]);
        if (! empty($data['mes'])) {
            $start = CarbonImmutable::createFromFormat('!Y-m', $data['mes']);
            $query->whereBetween('data', [$start->toDateString(), $start->endOfMonth()->toDateString()]);
        }

        return response()->json($query->orderByDesc('data')->orderByDesc('id')->get());
    }

    public function storeExpense(Request $request, string $moto)
    {
        $record = $this->moto($request, $moto);

        $data = $this->expenseData($request);

        return DB::transaction(function () use ($request, $moto, $data) {
            $motorcycle = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            $record = $motorcycle->despesas()->create($data);
            $this->fuelSideEffects($request, $motorcycle, $data, [$data['posto_id']]);

            return response()->json($record, 201);
        }, 3);
    }

    public function updateExpense(Request $request, string $moto, string $despesa)
    {
        $record = $this->moto($request, $moto)->despesas()->findOrFail($despesa);
        $data = $this->expenseData($request);

        return DB::transaction(function () use ($request, $moto, $despesa, $data) {
            $motorcycle = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            $record = $motorcycle->despesas()->findOrFail($despesa);
            $oldStation = $record->posto_id;
            $record->update($data);
            $this->fuelSideEffects($request, $motorcycle, $data, [$oldStation, $data['posto_id']]);

            return response()->json($record->refresh());
        }, 3);
    }

    public function deleteExpense(Request $request, string $moto, string $despesa)
    {
        $this->moto($request, $moto)->despesas()->findOrFail($despesa);
        DB::transaction(function () use ($request, $moto, $despesa) {
            $motorcycle = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            $record = $motorcycle->despesas()->findOrFail($despesa);
            $station = $record->posto_id;
            $record->delete();
            $this->fuelSideEffects($request, $motorcycle, [], [$station]);
        }, 3);

        return response()->noContent();
    }

    private function fuelSideEffects(Request $request, $motorcycle, array $data, array $stations): void
    {
        if (($data['km_abastecimento'] ?? 0) > $motorcycle->km_atual) {
            $motorcycle->update(['km_atual' => $data['km_abastecimento']]);
        }
        $ids = array_unique(array_filter($stations));
        sort($ids);
        foreach ($ids as $id) {
            $station = $request->user()->postos()->lockForUpdate()->findOrFail($id);
            app(FuelStationMemory::class)->refresh($station);
        }
    }

    public function reports(Request $request)
    {
        $data = $request->validate(['inicio' => ['required', 'date_format:Y-m-d'], 'fim' => ['required', 'date_format:Y-m-d', 'after_or_equal:inicio'], 'motocicleta_id' => ['nullable', 'integer', 'min:1']]);
        if (CarbonImmutable::parse($data['inicio'])->addYears(10)->lt(CarbonImmutable::parse($data['fim']))) {
            throw ValidationException::withMessages(['fim' => 'Selecione um período de até dez anos.']);
        }
        $motos = $request->user()->motocicletas();
        if (! empty($data['motocicleta_id'])) {
            $motos->whereKey($this->moto($request, (string) $data['motocicleta_id'])->id);
        }
        $motos = $motos->get();
        $ids = $motos->pluck('id');
        $expenses = Despesa::whereIn('motocicleta_id', $ids)->whereBetween('data', [$data['inicio'], $data['fim']])->orderByDesc('data')->orderByDesc('id')->get();
        $maintenance = Manutencao::whereIn('motocicleta_id', $ids)->whereBetween('data', [$data['inicio'], $data['fim']])->orderByDesc('data')->orderByDesc('id')->get();
        $alerts = Alerta::whereIn('motocicleta_id', $ids)->orderBy('id')->get();
        $months = [];
        $cursor = CarbonImmutable::parse($data['inicio'])->startOfMonth();
        $end = CarbonImmutable::parse($data['fim']);
        while ($cursor->lte($end)) {
            $month = $cursor->format('Y-m');
            $e = $expenses->filter(fn ($r) => $r->data->format('Y-m') === $month);
            $m = $maintenance->filter(fn ($r) => $r->data->format('Y-m') === $month && $r->status === 'concluido');
            $months[] = ['mes' => $month, 'combustivel' => Money::sum($e->where('tipo_despesa', 'combustivel')), 'reparos' => Money::sum($e->where('tipo_despesa', 'reparo')), 'outras_despesas' => Money::sum($e->whereNotIn('tipo_despesa', ['combustivel', 'reparo'])), 'manutencoes' => Money::sum($m), 'total' => Money::decimal(Money::cents(Money::sum($e)) + Money::cents(Money::sum($m)))];
            $cursor = $cursor->addMonth();
        }
        $completed = $maintenance->where('status', 'concluido');

        $insights = app(MotorcycleInsights::class);

        return response()->json(['categorias' => $insights->spending($expenses, $maintenance), 'estatisticas' => $motos->map(fn ($moto) => ['motocicleta_id' => $moto->id, ...$insights->fuel($moto, $data['inicio'], $data['fim'])]), 'precos_mensais' => $insights->prices($expenses), 'motocicletas' => $motos, 'despesas' => $expenses, 'manutencoes' => $maintenance, 'alertas' => $alerts, 'mensal' => $months, 'resumo' => [
            'combustivel' => Money::sum($expenses->where('tipo_despesa', 'combustivel')), 'reparos' => Money::sum($expenses->where('tipo_despesa', 'reparo')),
            'despesas' => Money::sum($expenses), 'manutencoes' => Money::sum($completed), 'total' => Money::decimal(Money::cents(Money::sum($expenses)) + Money::cents(Money::sum($completed))),
        ]]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate(['nome' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($user->id)], 'telefone' => ['nullable', 'string', 'max:30']]);
        if ($data['email'] !== $user->email) {
            $request->validate(['current_password' => ['bail', 'required', 'string', 'max:255', 'current_password']]);
        }
        $user->update($data);

        return response()->json($user);
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => ['bail', 'required', 'string', 'max:255', 'current_password'], 'password' => ['required', 'string', 'min:8', new NewPassword, 'confirmed', 'different:current_password']]);
        $request->user()->update(['password' => $data['password']]);
        $request->session()->regenerate();
        $request->session()->put('password_hash_web', $request->user()->password);

        return response()->json(['message' => 'Senha alterada.']);
    }

    public function alerts(Request $request, string $moto)
    {
        return response()->json($this->moto($request, $moto)->alertas()->orderByDesc('id')->get());
    }

    private function alertData(Request $request, $motorcycle, ?Alerta $existing = null): array
    {
        $data = $request->validate(['tipo_alerta' => ['required', Rule::in(array_keys(MotorcycleServices::alerts()))], 'km_limite' => ['nullable', 'required_without:data_limite', 'integer', 'between:1,4294967295'], 'km_base' => ['nullable', 'integer', 'between:0,4294967295'], 'data_limite' => ['nullable', 'required_without:km_limite', 'date_format:Y-m-d']]);
        $base = null;
        if (! empty($data['km_limite'])) {
            $currentKm = (int) $motorcycle->refresh()->km_atual;
            $base = (int) ($data['km_base'] ?? $existing?->km_base ?? ($currentKm < $data['km_limite'] ? $currentKm : 0));
            if ($base >= $data['km_limite'] || $base > $currentKm) {
                throw ValidationException::withMessages(['km_base' => 'O KM de referência deve ser menor que o limite e não pode superar o odômetro atual.']);
            }
        } elseif (isset($data['km_base'])) {
            throw ValidationException::withMessages(['km_base' => 'Informe um limite de KM para usar uma referência.']);
        }

        return [...$data, 'km_base' => $base, 'km_limite' => $data['km_limite'] ?? null, 'data_limite' => $data['data_limite'] ?? null];
    }

    public function storeAlert(Request $request, string $moto)
    {
        $record = $this->moto($request, $moto);
        $data = $this->alertData($request, $record);

        return response()->json($record->alertas()->create([...$data, 'status' => 'pendente', 'nivel_urgencia' => 'media']), 201);
    }

    public function updateAlert(Request $request, string $moto, string $alerta)
    {
        $motorcycle = $this->moto($request, $moto);
        $alerta = (string) $motorcycle->alertas()->findOrFail($alerta)->getKey();

        return Cache::lock('alerta.send.'.$alerta, 120)->block(5, function () use ($request, $motorcycle, $alerta) {
            $record = $motorcycle->alertas()->findOrFail($alerta);
            if ($request->input('status') === 'concluido') {
                $record->update(['status' => 'concluido']);
            } else {
                if ($record->status !== 'pendente') {
                    throw ValidationException::withMessages(['status' => 'Um alerta notificado ou concluído não pode ser reativado. Crie o próximo alerta.']);
                }
                $record->update($this->alertData($request, $motorcycle, $record));
            }

            return response()->json($record->refresh());
        });
    }

    public function deleteAlert(Request $request, string $moto, string $alerta)
    {
        $motorcycle = $this->moto($request, $moto);
        $alerta = (string) $motorcycle->alertas()->findOrFail($alerta)->getKey();

        return Cache::lock('alerta.send.'.$alerta, 120)->block(5, function () use ($motorcycle, $alerta) {
            $motorcycle->alertas()->findOrFail($alerta)->delete();

            return response()->noContent();
        });
    }

    private function subscriptionData(Request $request): array
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (! PushEndpoint::allowed($value)) {
                    $fail('O endereço de notificações não é suportado.');
                }
            }],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{87}=?$/'],
            'keys.auth' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{22}(==)?$/'],
        ]);
        $public = base64_decode(strtr(rtrim($data['keys']['p256dh'], '='), '-_', '+/').'=', true);
        if (! $public || strlen($public) !== 65 || ord($public[0]) !== 4) {
            throw ValidationException::withMessages(['keys.p256dh' => 'Chave do navegador inválida.']);
        }
        $auth = base64_decode(strtr(rtrim($data['keys']['auth'], '='), '-_', '+/').'==', true);
        $pem = "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode(hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200').$public), 64, "\n").'-----END PUBLIC KEY-----';
        if (! $auth || strlen($auth) !== 16 || ! openssl_pkey_get_public($pem)) {
            throw ValidationException::withMessages(['keys' => 'Chaves do navegador inválidas.']);
        }

        return ['endpoint' => $data['endpoint'], 'keys' => ['p256dh' => $data['keys']['p256dh'], 'auth' => $data['keys']['auth']]];
    }

    public function subscribe(Request $request)
    {
        $data = $this->subscriptionData($request);
        $hash = hash('sha256', $data['endpoint']);

        return Cache::lock('push.subscription.'.$hash, 10)->block(5, function () use ($request, $data, $hash) {
            DB::transaction(function () use ($request, $data, $hash) {
                User::where('push_endpoint_hash', $hash)->where('id', '!=', $request->user()->id)->update(['push_subscription' => null, 'push_endpoint_hash' => null]);
                $request->user()->update(['push_subscription' => $data, 'push_endpoint_hash' => $hash]);
            });

            return response()->json(['message' => 'Notificações ativadas neste navegador.']);
        });
    }

    public function unsubscribe(Request $request)
    {
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);
        $request->user()->whereKey($request->user()->id)->where('push_endpoint_hash', hash('sha256', $data['endpoint']))->update(['push_subscription' => null, 'push_endpoint_hash' => null]);

        return response()->noContent();
    }

    public function pushConfig(Request $request, WebPushConfiguration $configuration)
    {
        return response()->json([...$configuration->status(), 'subscriptionRegistered' => filled($request->user()->push_subscription)]);
    }

    public function testPush(Request $request, WebPushService $push, WebPushConfiguration $configuration)
    {
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);
        $hash = hash('sha256', $data['endpoint']);
        if (! $request->user()->push_subscription || ! hash_equals((string) $request->user()->push_endpoint_hash, $hash)) {
            throw ValidationException::withMessages(['endpoint' => 'Ative este navegador antes de enviar o teste.']);
        }
        abort_unless($configuration->status()['configured'], 503, 'Notificações indisponíveis neste ambiente.');
        try {
            $push->sendTest($request->user());
        } catch (Throwable $exception) {
            Log::warning('Falha no teste Web Push.', ['exception_type' => $exception::class]);

            return response()->json(['message' => 'Não foi possível enviar o teste. Ative este navegador novamente ou tente mais tarde.'], 503);
        }

        return response()->json(['message' => 'Teste aceito pelo serviço do navegador. Confira o recebimento.'], 202);
    }
}
