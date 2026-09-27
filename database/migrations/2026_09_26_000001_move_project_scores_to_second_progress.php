<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('notas_alumnos')
            ->whereIn('categoria_id', DB::table('categorias_evaluacion')->where('tipo_calculo', 'proyecto')->select('id'))
            ->update(['promedio_1' => null]);
    }

    public function down(): void
    {
        DB::table('notas_alumnos')
            ->whereIn('categoria_id', DB::table('categorias_evaluacion')->where('tipo_calculo', 'proyecto')->select('id'))
            ->update(['promedio_1' => DB::raw('promedio_2')]);
    }
};
