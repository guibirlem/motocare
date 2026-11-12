<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_converts_and_formats_cents_without_float_rounding(): void
    {
        $this->assertSame(12590, Money::cents('125.90'));
        $this->assertSame('125.90', Money::decimal(12590));
    }
}
