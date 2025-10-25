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

        if (Schema::hasColumn('inmuebles', 'tags')) {
            return;
        }

        Schema::table('inmuebles', function (Blueprint $table): void {
            $table->json('tags')->nullable()->after('extras');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inmuebles')) {
            return;
        }

        if (! Schema::hasColumn('inmuebles', 'tags')) {
            return;
        }

        Schema::table('inmuebles', function (Blueprint $table): void {
            $table->dropColumn('tags');
        });
    }
};
