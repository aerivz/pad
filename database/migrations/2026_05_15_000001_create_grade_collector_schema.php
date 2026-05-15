<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias_evaluacion', function (Blueprint $table) {
            $table->foreignId('asignacion_id')->nullable()->after('id')->constrained('asignaciones');
            $table->foreignId('trimestre_id')->nullable()->after('asignacion_id')->constrained('trimestres');
            $table->enum('tipo_calculo', ['normal', 'laboratorio'])->default('normal')->after('porcentaje');
            $table->unsignedTinyInteger('cantidad_notas')->default(4)->after('tipo_calculo');
            $table->unsignedInteger('orden')->default(1)->after('cantidad_notas');
            $table->boolean('activo')->default(true)->after('orden');
        });

        Schema::create('notas_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias_evaluacion');
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->decimal('nota_1', 5, 2)->nullable();
            $table->decimal('nota_2', 5, 2)->nullable();
            $table->decimal('nota_3', 5, 2)->nullable();
            $table->decimal('nota_4', 5, 2)->nullable();
            $table->decimal('promedio_1', 8, 2)->nullable();
            $table->decimal('promedio_2', 8, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->unique(['categoria_id', 'alumno_id'], 'uq_categoria_alumno');
        });

        Schema::create('conducta_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones');
            $table->foreignId('trimestre_id')->constrained('trimestres');
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->decimal('valor', 5, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->unique(['asignacion_id', 'trimestre_id', 'alumno_id'], 'uq_conducta_asignacion_trimestre_alumno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conducta_alumnos');
        Schema::dropIfExists('notas_alumnos');

        Schema::table('categorias_evaluacion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trimestre_id');
            $table->dropConstrainedForeignId('asignacion_id');
            $table->dropColumn(['tipo_calculo', 'cantidad_notas', 'orden', 'activo']);
        });
    }
};
