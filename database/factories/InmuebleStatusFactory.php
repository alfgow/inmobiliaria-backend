<?php

namespace Database\Factories;

use App\Models\InmuebleStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\InmuebleStatus>
 */
class InmuebleStatusFactory extends Factory
{
    protected $model = InmuebleStatus::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word(),
            'descripcion' => $this->faker->sentence(),
            'color' => $this->faker->safeHexColor(),
            'orden' => $this->faker->numberBetween(1, 10),
        ];
    }
}
