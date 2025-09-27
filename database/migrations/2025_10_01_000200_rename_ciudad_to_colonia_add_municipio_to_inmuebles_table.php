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
        Schema::table('inmuebles', function (Blueprint $table): void {
            $table->renameColumn('ciudad', 'colonia');
            $table->string('municipio', 120)->nullable()->after('colonia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inmuebles', function (Blueprint $table): void {
            $table->dropColumn('municipio');
            $table->renameColumn('colonia', 'ciudad');
        });
    }
};
