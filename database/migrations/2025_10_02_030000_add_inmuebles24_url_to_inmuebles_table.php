<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inmuebles')) {
            return;
        }

        if (Schema::hasColumn('inmuebles', 'inmuebles24_url')) {
            return;
        }

        Schema::table('inmuebles', function (Blueprint $table): void {
            $table->string('inmuebles24_url', 500)->nullable()->after('tour_virtual_url');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inmuebles')) {
            return;
        }

        if (! Schema::hasColumn('inmuebles', 'inmuebles24_url')) {
            return;
        }

        Schema::table('inmuebles', function (Blueprint $table): void {
            $table->dropColumn('inmuebles24_url');
        });
    }
};
