<?php

namespace App\Services;

use App\Models\Despesa;
use App\Models\Posto;

class FuelStationMemory
{
    public const FUELS = ['gasolina', 'gasolina_aditivada', 'etanol', 'diesel'];

    // O nome livre nos lançamentos continua sendo um retrato do posto na época.
    public function refresh(Posto $station): void
    {
        foreach (self::FUELS as $fuel) {
            $latest = Despesa::where('posto_id', $station->id)->where('tipo_despesa', 'combustivel')
                ->where('combustivel', $fuel)->orderByDesc('data')->orderByDesc('id')->lockForUpdate()->first();
            if ($latest) {
                $station->precos()->updateOrCreate(['combustivel' => $fuel], ['preco_litro' => $latest->preco_litro]);
            } else {
                $station->precos()->where('combustivel', $fuel)->delete();
            }
        }
    }
}
