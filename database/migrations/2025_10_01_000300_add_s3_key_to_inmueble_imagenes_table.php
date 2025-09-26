<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('inmueble_imagenes', 's3_key')) {
            Schema::table('inmueble_imagenes', function (Blueprint $table) {
                $table->string('s3_key')->nullable()->after('path');
            });
        }

        DB::table('inmueble_imagenes')
            ->where(function ($query) {
                $query->whereNull('s3_key')
                    ->orWhere('s3_key', '');
            })
            ->whereNotNull('path')
            ->update(['s3_key' => DB::raw('path')]);

        DB::statement('ALTER TABLE inmueble_imagenes MODIFY s3_key VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('inmueble_imagenes', 's3_key')) {
            Schema::table('inmueble_imagenes', function (Blueprint $table) {
                $table->dropColumn('s3_key');
            });
        }
    }
};
