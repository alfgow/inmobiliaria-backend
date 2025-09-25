<?php

namespace Database\Seeders;

use App\Models\InmuebleStatus;
use Illuminate\Database\Seeder;

class InmuebleStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['nombre' => 'Disponible', 'descripcion' => 'Listo para mostrar', 'color' => '#10b981', 'orden' => 1],
            ['nombre' => 'En negociación', 'descripcion' => 'Interés activo', 'color' => '#f97316', 'orden' => 2],
            ['nombre' => 'Apartado', 'descripcion' => 'Reservado temporalmente', 'color' => '#6366f1', 'orden' => 3],
            ['nombre' => 'Vendido', 'descripcion' => 'Cerrado exitosamente', 'color' => '#8b5cf6', 'orden' => 4],
            ['nombre' => 'Rentado', 'descripcion' => 'Contrato activo', 'color' => '#0ea5e9', 'orden' => 5],
        ];

        foreach ($statuses as $status) {
            InmuebleStatus::updateOrCreate(
                ['nombre' => $status['nombre']],
                $status,
            );
        }
    }
}
