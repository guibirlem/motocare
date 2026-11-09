<?php

namespace App\Http\Controllers;

use App\Services\MotorcycleInsights;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InsightsController extends Controller
{
    public function stations(Request $request)
    {
        return response()->json($request->user()->postos()->with(['precos' => fn ($query) => $query->orderBy('id')])->orderBy('nome')->get());
    }

    public function storeStation(Request $request)
    {
        if (is_string($request->input('nome'))) {
            $request->merge(['nome' => preg_replace('/\s+/u', ' ', trim($request->input('nome')))]);
        }
        $data = $request->validate(['nome' => ['required', 'string', 'max:255', Rule::unique('postos')->where('usuario_id', $request->user()->id)]]);

        return response()->json($request->user()->postos()->create($data)->load('precos'), 201);
    }

    public function deleteStation(Request $request, string $posto)
    {
        $request->user()->postos()->findOrFail($posto)->delete();

        return response()->noContent();
    }

    public function dashboard(Request $request, string $moto, MotorcycleInsights $insights)
    {
        $motorcycle = $request->user()->motocicletas()->findOrFail($moto);
        $maintenance = $motorcycle->manutencoes()->whereDate('data', '<=', today()->toDateString())->get();
        $expenses = config('features.financeiro') ? $motorcycle->despesas()->whereDate('data', '<=', today()->toDateString())->get() : collect();
        $month = today()->format('Y-m');
        $monthCost = Money::cents(Money::sum($expenses->filter(fn ($row) => $row->data->format('Y-m') === $month))) + Money::cents(Money::sum($maintenance->filter(fn ($row) => $row->status === 'concluido' && $row->data->format('Y-m') === $month)));

        return response()->json([
            'gasto_mes' => Money::decimal($monthCost), 'corrente' => $insights->chain($motorcycle),
            'categorias' => $insights->spending($expenses, $maintenance),
            ...(config('features.financeiro') ? ['custo_km' => $insights->costPerKm($motorcycle, $expenses, $maintenance), ...$insights->fuel($motorcycle), 'despesas' => $expenses, 'precos_mensais' => $insights->prices($expenses)] : []),
        ]);
    }

    public function rain(Request $request, string $moto)
    {
        $request->user()->motocicletas()->findOrFail($moto);

        return DB::transaction(function () use ($request, $moto) {
            $motorcycle = $request->user()->motocicletas()->lockForUpdate()->findOrFail($moto);
            // O mesmo lembrete ativo é devolvido em repetição/duplo toque. O scheduler mantém o lock por alerta.
            $alert = $motorcycle->alertas()->where('origem', 'chuva_lavagem')->where('status', '!=', 'concluido')->first();
            if ($alert) {
                return response()->json($alert);
            }

            return response()->json($motorcycle->alertas()->create(['tipo_alerta' => 'lubrificacao_corrente', 'origem' => 'chuva_lavagem', 'data_limite' => today()->toDateString(), 'km_limite' => null, 'km_base' => null, 'status' => 'pendente', 'nivel_urgencia' => 'alta']), 201);
        }, 3);
    }
}
