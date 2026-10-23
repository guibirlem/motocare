<?php

namespace App\Http\Controllers;

use App\Models\Motocicleta;
use App\Models\User;
use App\Rules\NewPassword;
use App\Support\MotorcyclePlate;
use App\Support\MotorcycleServices;
use App\Support\TankConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HistoryController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', new NewPassword, 'confirmed'],
        ]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json($user, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email', 'max:255'], 'password' => ['required', 'string', 'max:255']]);
        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha inválidos.']);
        }
        $request->session()->regenerate();

        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->update(['push_subscription' => null, 'push_endpoint_hash' => null]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sessão encerrada.']);
    }

    public function motorcycles(Request $request)
    {
        return response()->json($request->user()->motocicletas()->orderBy('id')->get());
    }

    public function storeMotorcycle(Request $request)
    {
        MotorcyclePlate::normalize($request);
        $data = $request->validate([
            'marca' => ['required', 'string', 'max:255'],
            'modelo' => ['required', 'string', 'max:255'],
            'ano' => ['required', 'integer', 'between:1900,2100'],
            'placa' => ['required', 'string', 'regex:/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', 'unique:motocicletas,placa'],
            'km_atual' => ['required', 'integer', 'between:0,4294967295'],
            ...TankConfiguration::rules(),
        ]);

        TankConfiguration::check($data);

        return response()->json($request->user()->motocicletas()->create($data), 201);
    }

    private function motorcycle(Request $request, string $moto)
    {
        return $request->user()->motocicletas()->findOrFail($moto);
    }

    private function maintenanceData(Request $request): array
    {
        $data = $request->validate([
            'tipo_servico' => ['required', Rule::in(array_keys(MotorcycleServices::LABELS))],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'natureza' => ['nullable', Rule::in(['preventiva', 'corretiva'])],
            'valor' => ['required', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
            'status' => ['required', Rule::in(['pendente', 'concluido'])],
            'data' => ['required', 'date_format:Y-m-d', Rule::when($request->input('status') === 'concluido', ['before_or_equal:today'])],
            'km_servico' => ['nullable', 'required_if:status,concluido', 'integer', 'between:0,4294967295'],
        ]);

        return [...$data, 'descricao' => $data['descricao'] ?? null, 'km_servico' => $data['km_servico'] ?? null];
    }

    public function index(Request $request, string $moto)
    {
        return response()->json($this->motorcycle($request, $moto)->manutencoes()->orderByDesc('data')->orderByDesc('id')->get());
    }

    public function store(Request $request, string $moto)
    {
        $this->motorcycle($request, $moto);
        $data = $this->maintenanceData($request);

        return DB::transaction(function () use ($request, $moto, $data) {
            $motorcycle = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            $record = $motorcycle->manutencoes()->create($data);
            $this->advanceOdometer($motorcycle, $data);

            return response()->json($record, 201);
        }, 3);
    }

    public function update(Request $request, string $moto, string $manutencao)
    {
        $this->motorcycle($request, $moto)->manutencoes()->findOrFail($manutencao);
        $data = $this->maintenanceData($request);

        return DB::transaction(function () use ($request, $moto, $manutencao, $data) {
            $motorcycle = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            $record = $motorcycle->manutencoes()->findOrFail($manutencao);
            $record->update($data);
            $this->advanceOdometer($motorcycle, $data);

            return response()->json($record->refresh());
        }, 3);
    }

    private function advanceOdometer(Motocicleta $motorcycle, array $data): void
    {
        if ($data['status'] === 'concluido' && $data['km_servico'] > $motorcycle->km_atual) {
            $motorcycle->update(['km_atual' => $data['km_servico']]);
        }
    }

    public function destroy(Request $request, string $moto, string $manutencao)
    {
        $this->motorcycle($request, $moto)->manutencoes()->findOrFail($manutencao)->delete();

        return response()->noContent();
    }
}
