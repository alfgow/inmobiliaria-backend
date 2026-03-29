<?php

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::create('contactos', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
        $table->string('email')->nullable();
        $table->string('telefono')->nullable();
        $table->timestamps();
    });

    Schema::create('comentarios', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('contacto_id');
        $table->text('comentario');
        $table->timestamps();
    });

    Schema::create('intereses', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('contacto_id');
        $table->foreignId('inmueble_id')->nullable();
        $table->timestamps();
    });
});

it('encuentra contactos al buscar un telefono con espacios', function () {
    $user = User::factory()->create();

    Contact::create([
        'nombre' => 'Juan Perez',
        'email' => 'juan@example.com',
        'telefono' => '5559177781',
    ]);

    $response = $this->actingAs($user)->get(route('contactos.index', [
        'search' => '55 59 17 77 81',
    ]));

    $response->assertOk();
    $response->assertSeeText('Juan Perez');
});

it('encuentra contactos al buscar un telefono con prefijo +52 1', function () {
    $user = User::factory()->create();

    Contact::create([
        'nombre' => 'Maria Lopez',
        'email' => 'maria@example.com',
        'telefono' => '5559177781',
    ]);

    $response = $this->actingAs($user)->get(route('contactos.index', [
        'search' => '+5215559177781',
    ]));

    $response->assertOk();
    $response->assertSeeText('Maria Lopez');
});
