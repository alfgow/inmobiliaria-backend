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
        if (Schema::hasColumn('inmuebles', 'ciudad') && !Schema::hasColumn('inmuebles', 'colonia')) {
            Schema::table('inmuebles', function (Blueprint $table): void {
                $table->renameColumn('ciudad', 'colonia');
            });
        }

        if (!Schema::hasColumn('inmuebles', 'municipio')) {
            Schema::table('inmuebles', function (Blueprint $table): void {
                $table->string('municipio', 120)->nullable()->after('colonia');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('inmuebles', 'municipio')) {
            Schema::table('inmuebles', function (Blueprint $table): void {
                $table->dropColumn('municipio');
            });
        }

        if (Schema::hasColumn('inmuebles', 'colonia') && !Schema::hasColumn('inmuebles', 'ciudad')) {
            Schema::table('inmuebles', function (Blueprint $table): void {
                $table->renameColumn('colonia', 'ciudad');
            });
        }
    }
};