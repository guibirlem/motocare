<?php

namespace App\Support;

use Illuminate\Http\Request;

class MotorcyclePlate
{
    public static function normalize(Request $request): void
    {
        if (is_string($request->input('placa'))) {
            $request->merge(['placa' => strtoupper(preg_replace('/[-\s]/', '', $request->input('placa')))]);
        }
    }
}
