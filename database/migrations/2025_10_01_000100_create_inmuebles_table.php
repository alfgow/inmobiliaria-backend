<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inmuebles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('users');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 12, 2);
            $table->string('direccion', 255);
            $table->string('ciudad', 120)->nullable();
            $table->string('estado', 120)->nullable();
            $table->string('codigo_postal', 20)->nullable();
            $table->enum('tipo', [
                'Departamento',
                'Casa',
                'Oficina',
                'Local Comercial',
                'Terreno',
                'Bodega',
                'Otro',
            ]);
            $table->enum('operacion', ['Renta', 'Venta', 'Traspaso']);
            $table->unsignedTinyInteger('estatus_id');
            $table->unsignedTinyInteger('habitaciones')->nullable();
            $table->unsignedTinyInteger('banos')->nullable();
            $table->unsignedTinyInteger('estacionamientos')->nullable();
            $table->decimal('metros_cuadrados', 8, 2)->nullable();
            $table->decimal('superficie_construida', 8, 2)->nullable();
            $table->decimal('superficie_terreno', 8, 2)->nullable();
            $table->unsignedSmallInteger('anio_construccion')->nullable();
            $table->boolean('destacado')->default(false);
            $table->string('video_url')->nullable();
            $table->string('tour_virtual_url')->nullable();
            $table->json('amenidades')->nullable();
            $table->json('extras')->nullable();
            $table->timestamps();

            $table->foreign('estatus_id')->references('id')->on('inmueble_estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inmuebles');
    }
};
