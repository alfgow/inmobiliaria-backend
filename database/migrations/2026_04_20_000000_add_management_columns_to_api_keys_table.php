<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('api_keys', 'allowed_ip')) {
            Schema::table('api_keys', function (Blueprint $table): void {
                $table->string('allowed_ip', 45)->nullable();
            });
        }

        if (! Schema::hasColumn('api_keys', 'status')) {
            Schema::table('api_keys', function (Blueprint $table): void {
                $table->string('status', 16)->default('active');
            });
        }

        if (! Schema::hasColumn('api_keys', 'suspended_at')) {
            Schema::table('api_keys', function (Blueprint $table): void {
                $table->timestamp('suspended_at')->nullable();
            });
        }

        if (! Schema::hasColumn('api_keys', 'revoked_at')) {
            Schema::table('api_keys', function (Blueprint $table): void {
                $table->timestamp('revoked_at')->nullable();
            });
        }

        DB::table('api_keys')
            ->whereNull('status')
            ->update(['status' => 'active']);
    }

    public function down(): void
    {
        $columns = [];

        foreach (['allowed_ip', 'status', 'suspended_at', 'revoked_at'] as $column) {
            if (Schema::hasColumn('api_keys', $column)) {
                $columns[] = $column;
            }
        }

        if ($columns !== []) {
            Schema::table('api_keys', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
