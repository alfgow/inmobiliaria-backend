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
        Schema::create('inmueble_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inmueble_id')->constrained('inmuebles')->cascadeOnDelete();
            $table->string('disk', 50)->default('s3');
            $table->string('path');
            $table->string('url');
            $table->unsignedInteger('orden')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inmueble_imagenes');
    }
};
