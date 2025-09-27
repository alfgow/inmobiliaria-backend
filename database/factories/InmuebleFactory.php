<?php

namespace Database\Factories;

use App\Models\Inmueble;
use App\Models\InmuebleStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Inmueble>
 */
class InmuebleFactory extends Factory
{
    protected $model = Inmueble::class;

    public function definition(): array
    {
        $status = InmuebleStatus::query()->inRandomOrder()->first();

        if (! $status) {
            $status = InmuebleStatus::factory()->create();
        }

        return [
            'asesor_id' => User::factory(),
            'titulo' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph(),
            'precio' => $this->faker->randomFloat(2, 1000000, 20000000),
            'direccion' => $this->faker->streetAddress(),
            'colonia' => $this->faker->words(2, true),
            'municipio' => $this->faker->city(),
            'estado' => $this->faker->state(),
            'codigo_postal' => $this->faker->postcode(),
            'tipo' => $this->faker->randomElement(Inmueble::TIPOS),
            'operacion' => $this->faker->randomElement(Inmueble::OPERACIONES),
            'estatus_id' => $status->id,
            'habitaciones' => $this->faker->numberBetween(1, 5),
            'banos' => $this->faker->numberBetween(1, 4),
            'estacionamientos' => $this->faker->numberBetween(0, 3),
            'metros_cuadrados' => $this->faker->randomFloat(2, 40, 500),
            'destacado' => $this->faker->boolean(),
        ];
    }
}
