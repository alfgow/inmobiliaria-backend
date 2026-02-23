<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AuthenticateApiRequest;
use App\Http\Requests\Api\UpdateContactStatusRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContactStatusApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('contactos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 255)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('estado', 150)->nullable();
            $table->string('fuente', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('comentarios', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('contacto_id');
            $table->text('comentario');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('intereses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('contacto_id');
            $table->unsignedBigInteger('inmueble_id');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('interacciones_ia', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('contacto_id');
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('interacciones_ia');
        Schema::dropIfExists('intereses');
        Schema::dropIfExists('comentarios');
        Schema::dropIfExists('contactos');

        parent::tearDown();
    }

    public function test_update_status_accepts_all_valid_statuses(): void
    {
        $this->withoutMiddleware(AuthenticateApiRequest::class);

        foreach (UpdateContactStatusRequest::ESTADOS_VALIDOS as $estado) {
            $contactoId = $this->postJson('/api/v1/contactos', [
                'nombre' => 'Contacto '.$estado,
                'estado' => 'nuevo',
            ])->assertCreated()->json('data.id');

            $this->putJson('/api/v1/contactos/'.$contactoId.'/estado', [
                'estado' => $estado,
            ])
                ->assertOk()
                ->assertJsonPath('data.id', $contactoId)
                ->assertJsonPath('data.estado', $estado);
        }
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $this->withoutMiddleware(AuthenticateApiRequest::class);

        $contactoId = $this->postJson('/api/v1/contactos', [
            'nombre' => 'Contacto inválido',
            'estado' => 'nuevo',
        ])->assertCreated()->json('data.id');

        $this->putJson('/api/v1/contactos/'.$contactoId.'/estado', [
            'estado' => 'convertido',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['estado']);
    }
}
