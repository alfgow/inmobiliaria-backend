<?php

namespace Tests\Feature;

use App\Models\Inmueble;
use App\Models\InmuebleImage;
use App\Models\InmuebleStatus;
use App\Models\User;
use Database\Seeders\InmuebleStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InmuebleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'filesystems.default' => 's3',
            'filesystems.disks.s3.bucket' => 'testing-bucket',
        ]);
    }

    public function test_user_can_create_inmueble_with_images(): void
    {
        Storage::fake('s3');
        $this->seed(InmuebleStatusSeeder::class);

        $user = User::factory()->create();
        $status = InmuebleStatus::first();

        $response = $this->actingAs($user)->post(route('inmuebles.store'), [
            'titulo' => 'Casa en la playa',
            'precio' => '12000000',
            'direccion' => 'Av. del Sol 123',
            'ciudad' => 'Cancún',
            'estado' => 'Quintana Roo',
            'codigo_postal' => '77500',
            'tipo' => 'Casa',
            'operacion' => 'Venta',
            'estatus_id' => $status->id,
            'habitaciones' => 3,
            'banos' => 2,
            'estacionamientos' => 1,
            'amenidades' => "Alberca\nRoof Garden",
            'imagenes' => [
                UploadedFile::fake()->image('foto1.jpg', 1200, 800),
                UploadedFile::fake()->image('foto2.jpg', 1200, 800),
            ],
        ]);

        $response->assertRedirect(route('inmuebles.index'));

        $this->assertDatabaseHas('inmuebles', [
            'titulo' => 'Casa en la playa',
            'asesor_id' => $user->id,
            'operacion' => 'Venta',
        ]);

        $image = InmuebleImage::first();
        $this->assertNotNull($image);
        Storage::disk('s3')->assertExists($image->path);
    }

    public function test_user_can_update_inmueble_and_replace_images(): void
    {
        Storage::fake('s3');
        $this->seed(InmuebleStatusSeeder::class);

        $user = User::factory()->create();
        $status = InmuebleStatus::first();

        $inmueble = Inmueble::create([
            'asesor_id' => $user->id,
            'titulo' => 'Departamento céntrico',
            'precio' => 8500000,
            'direccion' => 'Av. Reforma 101',
            'ciudad' => 'CDMX',
            'estado' => 'Ciudad de México',
            'codigo_postal' => '06500',
            'tipo' => 'Departamento',
            'operacion' => 'Venta',
            'estatus_id' => $status->id,
        ]);

        $existingPath = "inmuebles/{$inmueble->id}/original.jpg";
        Storage::disk('s3')->put($existingPath, 'fake content');

        $image = $inmueble->images()->create([
            'disk' => 's3',
            'path' => $existingPath,
            'url' => Storage::disk('s3')->url($existingPath),
            'orden' => 1,
        ]);

        $response = $this->actingAs($user)->put(route('inmuebles.update', $inmueble), [
            'titulo' => 'Departamento remodelado',
            'precio' => '9500000',
            'direccion' => 'Av. Reforma 101',
            'ciudad' => 'CDMX',
            'estado' => 'Ciudad de México',
            'codigo_postal' => '06500',
            'tipo' => 'Departamento',
            'operacion' => 'Venta',
            'estatus_id' => $status->id,
            'habitaciones' => 2,
            'banos' => 2,
            'imagenes_eliminar' => [$image->id],
            'imagenes' => [
                UploadedFile::fake()->image('nueva.jpg', 1200, 800),
            ],
        ]);

        $response->assertRedirect(route('inmuebles.edit', $inmueble));

        $inmueble->refresh();

        $this->assertSame('Departamento remodelado', $inmueble->titulo);
        Storage::disk('s3')->assertMissing($existingPath);
        $this->assertCount(1, $inmueble->images);
    }

    public function test_user_can_delete_inmueble_and_images(): void
    {
        Storage::fake('s3');
        $this->seed(InmuebleStatusSeeder::class);

        $user = User::factory()->create();
        $status = InmuebleStatus::first();

        $inmueble = Inmueble::create([
            'asesor_id' => $user->id,
            'titulo' => 'Loft minimalista',
            'precio' => 4500000,
            'direccion' => 'Calle Arte 55',
            'ciudad' => 'Guadalajara',
            'estado' => 'Jalisco',
            'codigo_postal' => '44100',
            'tipo' => 'Departamento',
            'operacion' => 'Renta',
            'estatus_id' => $status->id,
        ]);

        $existingPath = "inmuebles/{$inmueble->id}/loft.jpg";
        Storage::disk('s3')->put($existingPath, 'fake content');

        $inmueble->images()->create([
            'disk' => 's3',
            'path' => $existingPath,
            'url' => Storage::disk('s3')->url($existingPath),
            'orden' => 1,
        ]);

        $response = $this->actingAs($user)->delete(route('inmuebles.destroy', $inmueble));

        $response->assertRedirect(route('inmuebles.index'));

        $this->assertDatabaseMissing('inmuebles', ['id' => $inmueble->id]);
        Storage::disk('s3')->assertMissing($existingPath);
    }
}
