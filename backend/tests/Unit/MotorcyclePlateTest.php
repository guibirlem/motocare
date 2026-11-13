<?php

namespace Tests\Unit;

use App\Support\MotorcyclePlate;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class MotorcyclePlateTest extends TestCase
{
    public function test_normalizes_spacing_dash_and_letter_case(): void
    {
        $request = Request::create('/', 'POST', ['placa' => ' abc-1d23 ']);

        MotorcyclePlate::normalize($request);

        $this->assertSame('ABC1D23', $request->input('placa'));
    }
}
