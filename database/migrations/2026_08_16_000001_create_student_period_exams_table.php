<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_periodo_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones');
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->enum('tipo', ['semestral', 'final']);
            $table->decimal('valor', 5, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->unique(['asignacion_id', 'alumno_id', 'tipo'], 'uq_eval_periodo_asignacion_alumno_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_periodo_alumnos');
    }
};
