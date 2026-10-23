<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class TankConfiguration
{
    public static function rules(): array
    {
        return [
            'capacidade_tanque' => ['nullable', 'numeric', 'decimal:0,2', 'between:0.01,100'],
            'volume_reserva' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,100'],
        ];
    }

    public static function check(array $data, $existing = null): void
    {
        $capacity = array_key_exists('capacidade_tanque', $data) ? $data['capacidade_tanque'] : $existing?->capacidade_tanque;
        $reserve = array_key_exists('volume_reserva', $data) ? $data['volume_reserva'] : $existing?->volume_reserva;
        if ($reserve !== null && ($capacity === null || (float) $reserve >= (float) $capacity)) {
            throw ValidationException::withMessages(['volume_reserva' => 'A reserva deve ser menor que a capacidade total do tanque.']);
        }
    }
}
