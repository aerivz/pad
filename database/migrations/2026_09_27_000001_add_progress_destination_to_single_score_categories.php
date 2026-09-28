<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias_evaluacion', function (Blueprint $table) {
            $table->enum('progreso_destino', ['progress_1', 'progress_2'])->nullable()->after('cantidad_notas');
        });

        Schema::table('plantillas_colector_detalle', function (Blueprint $table) {
            $table->enum('progreso_destino', ['progress_1', 'progress_2'])->nullable()->after('cantidad_notas');
        });

        DB::table('categorias_evaluacion')->where('tipo_calculo', 'proyecto')->update(['progreso_destino' => 'progress_2']);
        DB::table('plantillas_colector_detalle')->where('tipo_calculo', 'proyecto')->update(['progreso_destino' => 'progress_2']);
    }

    public function down(): void
    {
        Schema::table('plantillas_colector_detalle', function (Blueprint $table) {
            $table->dropColumn('progreso_destino');
        });

        Schema::table('categorias_evaluacion', function (Blueprint $table) {
            $table->dropColumn('progreso_destino');
        });
    }
};
