<?php

namespace Tests\Unit;

use App\Support\MotorcycleServices;
use PHPUnit\Framework\TestCase;

class MotorcycleServicesTest extends TestCase
{
    public function test_mvp_maintenance_and_reminder_categories_are_available(): void
    {
        $this->assertArrayHasKey('troca_oleo', MotorcycleServices::LABELS);
        $this->assertArrayHasKey('freios', MotorcycleServices::LABELS);
        $this->assertArrayHasKey('revisao', MotorcycleServices::alerts());
        $this->assertSame(['seguro', 'ipva', 'licenciamento'], MotorcycleServices::DATE_ALERTS);
    }
}
