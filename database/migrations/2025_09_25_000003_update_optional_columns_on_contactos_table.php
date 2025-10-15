<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contactos')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE contactos MODIFY inmueble_id BIGINT UNSIGNED NULL;');
            DB::statement('ALTER TABLE contactos MODIFY email VARCHAR(150) NULL;');
            DB::statement('ALTER TABLE contactos MODIFY telefono VARCHAR(20) NULL;');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE contactos ALTER COLUMN inmueble_id DROP NOT NULL;');
            DB::statement('ALTER TABLE contactos ALTER COLUMN email DROP NOT NULL;');
            DB::statement('ALTER TABLE contactos ALTER COLUMN telefono DROP NOT NULL;');
        } elseif ($driver === 'sqlite') {
            // SQLite no permite modificar la nulabilidad de una columna sin recrear la tabla.
            // Como esta migración está enfocada en entornos MySQL/PostgreSQL, omitimos cambios aquí.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('contactos')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE contactos MODIFY inmueble_id BIGINT UNSIGNED NOT NULL;');
            DB::statement('ALTER TABLE contactos MODIFY email VARCHAR(150) NOT NULL;');
            DB::statement('ALTER TABLE contactos MODIFY telefono VARCHAR(20) NOT NULL;');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE contactos ALTER COLUMN inmueble_id SET NOT NULL;');
            DB::statement('ALTER TABLE contactos ALTER COLUMN email SET NOT NULL;');
            DB::statement('ALTER TABLE contactos ALTER COLUMN telefono SET NOT NULL;');
        } elseif ($driver === 'sqlite') {
            // Sin cambios en SQLite por las mismas razones mencionadas en up().
        }
    }
};
