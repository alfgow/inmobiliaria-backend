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

it('muestra el nombre completo del contacto en el perfil', function () {
    $user = User::factory()->create();
    $contact = Contact::create([
        'nombre' => 'Juan Carlos Hernández López',
        'email' => 'juan@example.com',
        'telefono' => '5551234567',
    ]);

    $response = $this->actingAs($user)->get(route('contactos.show', $contact));

    $response->assertOk();
    $response->assertSeeText('Juan Carlos Hernández López');
});
