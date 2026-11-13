<?php

namespace Tests\Unit;

use App\Support\TankConfiguration;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TankConfigurationTest extends TestCase
{
    public function test_accepts_capacity_and_reserve_when_reserve_is_smaller(): void
    {
        $data = ['capacidade_tanque' => '12.00', 'volume_reserva' => '2.00'];

        $this->assertTrue(Validator::make($data, TankConfiguration::rules())->passes());
        TankConfiguration::check($data);
        $this->assertTrue(true);
    }

    public function test_rejects_negative_capacity_and_reserve_without_capacity_or_equal_to_capacity(): void
    {
        $this->assertFalse(Validator::make(['capacidade_tanque' => '-1'], TankConfiguration::rules())->passes());

        foreach ([['volume_reserva' => '2.00'], ['capacidade_tanque' => '2.00', 'volume_reserva' => '2.00']] as $data) {
            try {
                TankConfiguration::check($data);
                $this->fail('A configuracao de reserva deveria ser rejeitada.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('volume_reserva', $exception->errors());
            }
        }
    }
}
