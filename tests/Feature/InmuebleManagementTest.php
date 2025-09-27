<?php

namespace Tests\Feature;

use App\Models\Inmueble;
use App\Models\InmuebleImage;
use App\Models\InmuebleStatus;
use App\Models\User;
use App\Support\AddressSlugger;
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
            'colonia' => 'Centro',
            'municipio' => 'Benito Juárez',
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
            'colonia' => 'Centro',
            'municipio' => 'Benito Juárez',
        ]);

        $image = InmuebleImage::first();
        $this->assertNotNull($image);
        $this->assertNull($image->getRawOriginal('url'));
        $this->assertNotEmpty($image->url);
        $this->assertNotEmpty($image->temporaryVariantUrl('watermarked'));

        $expectedSlug = AddressSlugger::fromArray([
            'Av. del Sol 123',
            'Centro',
            'Benito Juárez',
            'Quintana Roo',
        ]);
        $this->assertStringStartsWith($expectedSlug . '/', $image->path);
        $this->assertStringContainsString('_watermarked.jpg', $image->path);

        $metadata = $image->metadata;
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('variants', $metadata);

        foreach (['original', 'normalized', 'watermarked', 'thumbnail'] as $variant) {
            $this->assertArrayHasKey($variant, $metadata['variants']);
            $variantPath = $metadata['variants'][$variant]['path'];
            Storage::disk('s3')->assertExists($variantPath);
        }

        $this->assertSame(
            $metadata['variants']['watermarked']['path'],
            $image->path,
            'Watermarked path should be stored as main path.',
        );
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
            'colonia' => 'Colonia Juárez',
            'municipio' => 'Cuauhtémoc',
            'estado' => 'Ciudad de México',
            'codigo_postal' => '06500',
            'tipo' => 'Departamento',
            'operacion' => 'Venta',
            'estatus_id' => $status->id,
        ]);

        $basePath = AddressSlugger::fromArray([
            'Av. Reforma 101',
            'Colonia Juárez',
            'Cuauhtémoc',
            'Ciudad de México',
        ]);
        $existingPaths = [
            'original' => "$basePath/original_original.jpg",
            'normalized' => "$basePath/original_normalized.jpg",
            'watermarked' => "$basePath/original_watermarked.jpg",
            'thumbnail' => "$basePath/original_thumbnail.jpg",
        ];

        foreach ($existingPaths as $path) {
            Storage::disk('s3')->put($path, 'fake content');
        }

        $image = $inmueble->images()->create([
            'disk' => 's3',
            'path' => $existingPaths['watermarked'],
            'url' => null,
            'orden' => 1,
            'metadata' => [
                'variants' => [
                    'original' => ['path' => $existingPaths['original']],
                    'normalized' => ['path' => $existingPaths['normalized']],
                    'watermarked' => ['path' => $existingPaths['watermarked']],
                    'thumbnail' => ['path' => $existingPaths['thumbnail']],
                ],
            ],
        ]);

        $response = $this->actingAs($user)->put(route('inmuebles.update', $inmueble), [
            'titulo' => 'Departamento remodelado',
            'precio' => '9500000',
            'direccion' => 'Av. Reforma 101',
            'colonia' => 'Colonia Juárez',
            'municipio' => 'Cuauhtémoc',
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
        foreach ($existingPaths as $path) {
            Storage::disk('s3')->assertMissing($path);
        }
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
            'colonia' => 'Americana',
            'municipio' => 'Guadalajara',
            'estado' => 'Jalisco',
            'codigo_postal' => '44100',
            'tipo' => 'Departamento',
            'operacion' => 'Renta',
            'estatus_id' => $status->id,
        ]);

        $slugPath = AddressSlugger::fromArray([
            'Calle Arte 55',
            'Americana',
            'Guadalajara',
            'Jalisco',
        ]);
        $existingPaths = [
            'original' => "$slugPath/loft_original.jpg",
            'normalized' => "$slugPath/loft_normalized.jpg",
            'watermarked' => "$slugPath/loft_watermarked.jpg",
            'thumbnail' => "$slugPath/loft_thumbnail.jpg",
        ];

        foreach ($existingPaths as $path) {
            Storage::disk('s3')->put($path, 'fake content');
        }

        $inmueble->images()->create([
            'disk' => 's3',
            'path' => $existingPaths['watermarked'],
            'url' => null,
            'orden' => 1,
            'metadata' => [
                'variants' => [
                    'original' => ['path' => $existingPaths['original']],
                    'normalized' => ['path' => $existingPaths['normalized']],
                    'watermarked' => ['path' => $existingPaths['watermarked']],
                    'thumbnail' => ['path' => $existingPaths['thumbnail']],
                ],
            ],
        ]);

        $response = $this->actingAs($user)->delete(route('inmuebles.destroy', $inmueble));

        $response->assertRedirect(route('inmuebles.index'));

        $this->assertDatabaseMissing('inmuebles', ['id' => $inmueble->id]);
        foreach ($existingPaths as $path) {
            Storage::disk('s3')->assertMissing($path);
        }
    }
}
