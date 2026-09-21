<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias_evaluacion', function (Blueprint $table) {
            $table->enum('tipo_calculo', ['normal', 'laboratorio', 'proyecto'])->default('normal')->change();
        });

        Schema::table('plantillas_colector_detalle', function (Blueprint $table) {
            $table->enum('tipo_calculo', ['normal', 'laboratorio', 'proyecto'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('categorias_evaluacion', function (Blueprint $table) {
            $table->enum('tipo_calculo', ['normal', 'laboratorio'])->default('normal')->change();
        });

        Schema::table('plantillas_colector_detalle', function (Blueprint $table) {
            $table->enum('tipo_calculo', ['normal', 'laboratorio'])->change();
        });
    }
};
