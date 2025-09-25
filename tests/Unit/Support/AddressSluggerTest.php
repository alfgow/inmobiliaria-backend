<?php

namespace Tests\Unit\Support;

use App\Models\Inmueble;
use App\Support\AddressSlugger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressSluggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_slugifies_complete_address(): void
    {
        $inmueble = Inmueble::factory()->create([
            'direccion' => 'Av. Masaryk 123',
            'ciudad' => 'Polanco',
            'estado' => 'CDMX',
        ]);

        $slug = AddressSlugger::forInmueble($inmueble);

        $this->assertSame('av_masaryk_123_polanco_cdmx', $slug);
    }

    public function test_fallback_to_identifier_when_address_missing(): void
    {
        $inmueble = Inmueble::factory()->create([
            'direccion' => null,
            'ciudad' => null,
            'estado' => null,
        ]);

        $slug = AddressSlugger::forInmueble($inmueble);

        $this->assertSame('inmueble_' . $inmueble->id, $slug);
    }
}
