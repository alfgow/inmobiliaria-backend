<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('interacciones_ia')) {
            return;
        }

        Schema::create('interacciones_ia', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contacto_id')->constrained('contactos')->cascadeOnDelete();
            $table->json('payload');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('interacciones_ia')) {
            return;
        }

        Schema::dropIfExists('interacciones_ia');
    }
};
