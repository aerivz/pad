<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantillas_correo', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('cuerpo_html');
        });

        DB::table('plantillas_correo')->update(['activo' => true]);
    }

    public function down(): void
    {
        Schema::table('plantillas_correo', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
