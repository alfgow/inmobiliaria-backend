<?php

namespace Tests\Feature\Api;

use App\Http\Middleware\AuthenticateApiRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BotUserAndChatHistoryApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('bot_users', function (Blueprint $table): void {
            $table->string('session_id', 32)->primary();
            $table->string('status', 32)->default('new');
            $table->unsignedBigInteger('api_contact_id')->nullable();
            $table->string('nombre')->nullable();
            $table->string('telefono_real', 20)->nullable();
            $table->string('rol', 50)->nullable();
            $table->string('bot_status', 32)->default('free');
            $table->integer('rejected_count')->default(0);
            $table->string('questionnaire_status', 32)->default('none');
            $table->integer('current_question_index')->default(0);
            $table->string('property_id', 64)->nullable();
            $table->integer('count_outcontext')->default(0);
            $table->string('last_intencion', 64)->nullable();
            $table->string('last_accion', 64)->nullable();
            $table->text('last_bot_reply')->nullable();
            $table->integer('veces_pidiendo_nombre')->default(0);
            $table->integer('veces_pidiendo_telefono')->default(0);
            $table->timestamps();
        });

        Schema::create('n8n_chat_histories', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id', 32);
            $table->json('message');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('session_id')
                ->references('session_id')
                ->on('bot_users')
                ->cascadeOnDelete();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('n8n_chat_histories');
        Schema::dropIfExists('bot_users');

        parent::tearDown();
    }

    public function test_bot_users_crud_endpoints_work(): void
    {
        $this->withoutMiddleware(AuthenticateApiRequest::class);

        $createResponse = $this->postJson('/api/v1/bot-users', [
            'session_id' => '5215559177781',
            'status' => 'new',
            'nombre' => 'Alfonso',
            'telefono_real' => '5215559177781',
            'rol' => 'buyer',
            'current_question_index' => 3,
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.session_id', '5215559177781')
            ->assertJsonPath('data.nombre', 'Alfonso')
            ->assertJsonPath('data.current_question_index', 3);

        $this->getJson('/api/v1/bot-users?session_id=5215559')
            ->assertOk()
            ->assertJsonPath('data.0.session_id', '5215559177781');

        $this->getJson('/api/v1/bot-users/5215559177781')
            ->assertOk()
            ->assertJsonPath('data.session_id', '5215559177781');

        $this->patchJson('/api/v1/bot-users/5215559177781', [
            'status' => 'active',
            'bot_status' => 'busy',
            'current_question_index' => 4,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.bot_status', 'busy')
            ->assertJsonPath('data.current_question_index', 4);

        $this->deleteJson('/api/v1/bot-users/5215559177781')
            ->assertNoContent();
    }


    public function test_bot_user_show_resolves_normalized_phone_identifiers(): void
    {
        $this->withoutMiddleware(AuthenticateApiRequest::class);

        $this->postJson('/api/v1/bot-users', [
            'session_id' => 'whatsapp:+52 155 5917-7781@c.us',
            'status' => 'new',
            'telefono_real' => '+52 (155) 5917-7781',
        ])->assertCreated();

        $this->getJson('/api/v1/bot-users/5215559177781')
            ->assertOk()
            ->assertJsonPath('data.session_id', 'whatsapp:+52 155 5917-7781@c.us');
    }

    public function test_chat_histories_crud_endpoints_work(): void
    {
        $this->withoutMiddleware(AuthenticateApiRequest::class);

        $this->postJson('/api/v1/bot-users', [
            'session_id' => 'session-abc-123',
            'status' => 'new',
        ])->assertCreated();

        $created = $this->postJson('/api/v1/chat-histories', [
            'session_id' => 'session-abc-123',
            'message' => [
                'role' => 'user',
                'text' => 'Hola, quiero una casa en Puebla',
            ],
        ])->assertCreated();

        $historyId = $created->json('data.id');

        $this->getJson('/api/v1/chat-histories?session_id=session-abc-123')
            ->assertOk()
            ->assertJsonPath('data.0.session_id', 'session-abc-123');

        $this->getJson('/api/v1/chat-histories/'.$historyId)
            ->assertOk()
            ->assertJsonPath('data.id', $historyId);

        $this->patchJson('/api/v1/chat-histories/'.$historyId, [
            'message' => [
                'role' => 'assistant',
                'text' => 'Te ayudo con opciones en Puebla.',
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.message.role', 'assistant');

        $this->deleteJson('/api/v1/chat-histories/'.$historyId)
            ->assertNoContent();
    }
}
