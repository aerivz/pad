<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->boolean('es_electiva')->default(false)->after('activo');
        });

        Schema::create('asignacion_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->unique(['asignacion_id', 'alumno_id'], 'uq_asignacion_alumno');
        });

        Schema::create('evaluaciones_titular', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('secciones')->cascadeOnDelete();
            $table->foreignId('trimestre_id')->constrained('trimestres');
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->decimal('conducta', 5, 2)->nullable();
            $table->text('comentario')->nullable();
            $table->boolean('activo')->default(true);
            $table->unique(['seccion_id', 'trimestre_id', 'alumno_id'], 'uq_evaluacion_titular');
        });

        DB::table('conducta_alumnos as ca')
            ->join('alumnos as a', 'a.id', '=', 'ca.alumno_id')
            ->where('ca.activo', true)
            ->selectRaw('a.seccion_id, ca.trimestre_id, ca.alumno_id, ROUND(AVG(ca.valor), 2) as conducta')
            ->groupBy('a.seccion_id', 'ca.trimestre_id', 'ca.alumno_id')
            ->orderBy('ca.alumno_id')
            ->get()
            ->each(function ($row): void {
                DB::table('evaluaciones_titular')->insert([
                    'seccion_id' => $row->seccion_id,
                    'trimestre_id' => $row->trimestre_id,
                    'alumno_id' => $row->alumno_id,
                    'conducta' => $row->conducta,
                    'comentario' => null,
                    'activo' => true,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_titular');
        Schema::dropIfExists('asignacion_alumno');

        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn('es_electiva');
        });
    }
};
