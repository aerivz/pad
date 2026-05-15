<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones');
            $table->foreignId('trimestre_id')->constrained('trimestres');
            $table->foreignId('categoria_id')->constrained('categorias_evaluacion');
            $table->string('nombre', 120);
            $table->decimal('ponderacion', 6, 2);
            $table->unsignedInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
        });

        Schema::create('evaluacion_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained('evaluaciones');
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->decimal('valor', 5, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->unique(['evaluacion_id', 'alumno_id'], 'uq_evaluacion_alumno');
        });

        DB::table('trimestres')->updateOrInsert(
            ['numero' => 4],
            ['nombre' => 'Cuarto Trimestre']
        );

        DB::table('categorias_evaluacion')->updateOrInsert(
            ['nombre' => 'Laboratorios'],
            ['porcentaje' => 20.00]
        );

        DB::table('categorias_evaluacion')->updateOrInsert(
            ['nombre' => 'Actividades'],
            ['porcentaje' => 15.00]
        );

        $legacyNotes = DB::table('notas as n')
            ->join('categorias_evaluacion as c', 'c.id', '=', 'n.categoria_id')
            ->where('n.activo', true)
            ->select('n.id', 'n.alumno_id', 'n.asignacion_id', 'n.trimestre_id', 'n.categoria_id', 'n.valor', 'c.nombre', 'c.porcentaje')
            ->orderBy('n.id')
            ->get();

        $evaluationMap = [];

        foreach ($legacyNotes as $legacyNote) {
            $key = implode(':', [
                $legacyNote->asignacion_id,
                $legacyNote->trimestre_id,
                $legacyNote->categoria_id,
            ]);

            if (! isset($evaluationMap[$key])) {
                $evaluationId = DB::table('evaluaciones')->insertGetId([
                    'asignacion_id' => $legacyNote->asignacion_id,
                    'trimestre_id' => $legacyNote->trimestre_id,
                    'categoria_id' => $legacyNote->categoria_id,
                    'nombre' => $legacyNote->nombre.' consolidado',
                    'ponderacion' => (float) ($legacyNote->porcentaje ?? 0),
                    'orden' => (int) $legacyNote->categoria_id,
                    'activo' => true,
                ]);

                $evaluationMap[$key] = $evaluationId;
            }

            DB::table('evaluacion_notas')->updateOrInsert(
                [
                    'evaluacion_id' => $evaluationMap[$key],
                    'alumno_id' => $legacyNote->alumno_id,
                ],
                [
                    'valor' => $legacyNote->valor,
                    'activo' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluacion_notas');
        Schema::dropIfExists('evaluaciones');
    }
};
