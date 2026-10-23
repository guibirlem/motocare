<?php

namespace App\Services;

use App\Models\Motocicleta;
use App\Support\Money;
use Illuminate\Support\Collection;

class MotorcycleInsights
{
    public function fuel(Motocicleta $moto, ?string $start = null, ?string $end = null): array
    {
        $end ??= today()->toDateString();
        $rows = $moto->despesas()->where('tipo_despesa', 'combustivel')->whereDate('data', '<=', $end)->orderBy('data')->orderBy('id')->get();
        $anchor = null;
        $lastKm = null;
        $volume = 0;
        $cycles = [];
        foreach ($rows as $row) {
            $km = $row->km_abastecimento;
            if ($km === null || $row->tanque_cheio === null || ($lastKm !== null && $km < $lastKm)) {
                // Uma lacuna/odômetro regressivo invalida o trecho, nunca inventa consumo.
                $anchor = null;
                $volume = 0;
                $lastKm = $km;

                continue;
            }
            $lastKm = $km;
            if ($anchor !== null) {
                $volume += Money::cents($row->litros);
            }
            if ($row->tanque_cheio) {
                if ($anchor !== null && $km > $anchor->km_abastecimento && $volume > 0) {
                    $distance = $km - $anchor->km_abastecimento;
                    $cycles[] = ['data' => $row->data->toDateString(), 'km' => $distance, 'litros' => Money::decimal($volume), 'km_l' => round($distance * 100 / $volume, 2)];
                }
                $anchor = $row;
                $volume = 0;
            }
        }
        $selected = array_values(array_filter($cycles, fn ($cycle) => ! $start || $cycle['data'] >= $start));
        $liters = array_sum(array_map(fn ($cycle) => Money::cents($cycle['litros']), $selected));
        $average = $liters > 0 ? array_sum(array_column($selected, 'km')) * 100 / $liters : null;
        $recent = array_slice($cycles, -5);
        $recentLiters = array_sum(array_map(fn ($cycle) => Money::cents($cycle['litros']), $recent));
        $recentAverage = $recentLiters > 0 ? array_sum(array_column($recent, 'km')) * 100 / $recentLiters : null;
        $reserve = null;
        if ($anchor && $recentAverage > 0 && $moto->capacidade_tanque !== null && $moto->volume_reserva !== null && $moto->km_atual >= $lastKm) {
            $trip = $moto->km_atual - $anchor->km_abastecimento;
            $remaining = (float) $moto->capacidade_tanque;
            $previousKm = $anchor->km_abastecimento;
            $afterAnchor = false;
            foreach ($rows as $row) {
                if ($row->id === $anchor->id) {
                    $afterAnchor = true;

                    continue;
                }
                if ($afterAnchor) {
                    $remaining = min((float) $moto->capacidade_tanque, max(0, $remaining - ($row->km_abastecimento - $previousKm) / $recentAverage) + (float) $row->litros);
                    $previousKm = $row->km_abastecimento;
                }
            }
            $remaining = max(0, $remaining - ($moto->km_atual - $previousKm) / $recentAverage);
            $reserve = ['parcial_km' => $trip, 'litros_estimados' => round($remaining, 2), 'percentual' => round($remaining * 100 / (float) $moto->capacidade_tanque, 1), 'km_ate_reserva' => round(max(0, $remaining - (float) $moto->volume_reserva) * $recentAverage), 'km_ate_secar' => round($remaining * $recentAverage), 'consumo_referencia' => round($recentAverage, 2)];
        }

        return ['consumo_medio' => $average !== null ? round($average, 2) : null, 'ciclos' => $selected, 'reserva' => $reserve];
    }

    public function chain(Motocicleta $moto): array
    {
        $completed = $moto->manutencoes()->where('status', 'concluido')->orderBy('data')->orderBy('km_servico')->orderBy('id')->get();
        $kit = $completed->last(fn ($row) => $row->tipo_servico === 'kit_relacao');
        $adjustments = $kit ? $completed->filter(fn ($row) => $row->tipo_servico === 'ajuste_corrente' && [$row->data->toDateString(), $row->km_servico, $row->id] > [$kit->data->toDateString(), $kit->km_servico, $kit->id])->count() : null;

        return ['kit_instalado_em' => $kit?->data->toDateString(), 'km_instalacao' => $kit?->km_servico, 'ajustes_desde_troca' => $adjustments];
    }

    public function spending(Collection $expenses, Collection $maintenance): array
    {
        $completed = $maintenance->where('status', 'concluido');

        return [
            'combustivel' => Money::sum($expenses->where('tipo_despesa', 'combustivel')),
            'preventiva' => Money::sum($completed->where('natureza', 'preventiva')),
            'corretiva' => Money::decimal(Money::cents(Money::sum($completed->where('natureza', 'corretiva'))) + Money::cents(Money::sum($expenses->where('tipo_despesa', 'reparo')))),
            'documentacao' => Money::sum($expenses->where('tipo_despesa', 'documentacao')),
            'impostos' => Money::sum($expenses->where('tipo_despesa', 'impostos')),
            'outros' => Money::sum($expenses->where('tipo_despesa', 'outros')),
            'manutencao_sem_classificacao' => Money::sum($completed->whereNull('natureza')),
        ];
    }

    public function prices(Collection $expenses): array
    {
        return $expenses->filter(fn ($row) => $row->tipo_despesa === 'combustivel' && $row->posto_id && $row->combustivel)
            ->groupBy(fn ($row) => $row->posto_id.'|'.$row->combustivel.'|'.$row->data->format('Y-m'))
            ->map(function ($rows) {
                $row = $rows->first();

                return ['posto_id' => $row->posto_id, 'posto' => $row->posto, 'combustivel' => $row->combustivel, 'mes' => $row->data->format('Y-m'), 'preco_medio' => Money::decimal((int) round($rows->sum(fn ($item) => Money::cents($item->preco_litro)) / $rows->count()))];
            })->sortBy('mes')->values()->all();
    }

    public function costPerKm(Motocicleta $moto, Collection $expenses, Collection $maintenance): ?float
    {
        $readings = $expenses->filter(fn ($row) => $row->km_abastecimento !== null)->map(fn ($row) => ['data' => $row->data->toDateString(), 'km' => $row->km_abastecimento])
            ->concat($maintenance->where('status', 'concluido')->filter(fn ($row) => $row->km_servico !== null)->map(fn ($row) => ['data' => $row->data->toDateString(), 'km' => $row->km_servico]))->sortBy(fn ($row) => [$row['data'], $row['km']]);
        $first = $readings->first();
        if (! $first || $moto->km_atual <= $first['km']) {
            return null;
        }
        $cost = Money::cents(Money::sum($expenses->filter(fn ($row) => $row->data->toDateString() >= $first['data']))) + Money::cents(Money::sum($maintenance->filter(fn ($row) => $row->status === 'concluido' && $row->data->toDateString() >= $first['data'])));

        return round($cost / 100 / ($moto->km_atual - $first['km']), 2);
    }
}
