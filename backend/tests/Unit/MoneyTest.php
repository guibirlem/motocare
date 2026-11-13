<?php

namespace Tests\Unit;

use App\Support\Money;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_converts_and_formats_cents_without_float_rounding(): void
    {
        $this->assertSame(12590, Money::cents('125.90'));
        $this->assertSame('125.90', Money::decimal(12590));
    }

    public function test_money_sums_decimal_record_values_in_cents(): void
    {
        $records = new Collection([(object) ['valor' => '10.10'], (object) ['valor' => '0.20']]);

        $this->assertSame('10.30', Money::sum($records));
    }
}
