<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secciones', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('anio_escolar');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('apellidos');
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('nombre');
        });

        Schema::table('padres', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('email_principal');
        });
    }

    public function down(): void
    {
        Schema::table('padres', function (Blueprint $table) {
            $table->dropColumn('activo');
        });

        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn('activo');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('activo');
        });

        Schema::table('secciones', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
