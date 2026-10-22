<?php

namespace App\Support;

class Money
{
    public static function cents(string|int|float $value): int
    {
        [$whole,$fraction] = array_pad(explode('.', (string) $value, 2), 2, '');

        return (int) $whole * 100 + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function sum($records): string
    {
        return self::decimal($records->sum(fn ($row) => self::cents($row->valor)));
    }
}
