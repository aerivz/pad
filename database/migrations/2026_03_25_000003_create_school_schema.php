<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles');
            $table->string('nombre_usuario', 60)->unique();
            $table->string('email', 150)->unique();
            $table->string('password_hash');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('profesores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuarios')->nullOnDelete();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email', 150)->unique();
            $table->string('especialidad', 150)->nullable();
            $table->boolean('activo')->default(true);
        });

        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->nullable();
            $table->string('grado', 50)->nullable();
            $table->year('anio_escolar')->nullable();
        });

        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('secciones')->cascadeOnDelete();
            $table->string('nombres', 100)->nullable();
            $table->string('apellidos', 100)->nullable();
        });

        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
        });

        Schema::create('trimestres', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->nullable();
            $table->unsignedTinyInteger('numero')->nullable();
        });

        Schema::create('categorias_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
        });

        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->foreignId('materia_id')->constrained('materias');
            $table->foreignId('profesor_id')->constrained('profesores');
            $table->year('anio_escolar')->nullable();
        });

        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->foreignId('asignacion_id')->constrained('asignaciones');
            $table->foreignId('trimestre_id')->constrained('trimestres');
            $table->foreignId('categoria_id')->constrained('categorias_evaluacion');
            $table->decimal('valor', 5, 2)->nullable();
        });

        Schema::create('padres', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100)->nullable();
            $table->string('apellidos', 100)->nullable();
            $table->string('email_principal', 150)->nullable();
        });

        Schema::create('padre_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('padre_id')->constrained('padres')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->unique(['padre_id', 'alumno_id'], 'uq_padre_alumno');
        });

        Schema::create('plantillas_correo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('asunto', 255)->nullable();
            $table->text('cuerpo_html')->nullable();
        });

        Schema::create('envios_correo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('plantillas_correo');
            $table->foreignId('padre_id')->constrained('padres');
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->foreignId('trimestre_id')->constrained('trimestres');
            $table->enum('estado', ['pendiente', 'enviado', 'fallido'])->default('pendiente');
        });

        Schema::create('auditoria_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_id')->constrained('notas');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->decimal('valor_anterior', 5, 2)->nullable();
            $table->decimal('valor_nuevo', 5, 2)->nullable();
            $table->enum('accion', ['INSERT', 'UPDATE', 'DELETE']);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('notas', function (Blueprint $table) {
            $table->index('alumno_id', 'idx_notas_alumno');
        });

        Schema::table('envios_correo', function (Blueprint $table) {
            $table->index('estado', 'idx_envios_estado');
        });
    }

    public function down(): void
    {
        Schema::table('envios_correo', function (Blueprint $table) {
            $table->dropIndex('idx_envios_estado');
        });

        Schema::table('notas', function (Blueprint $table) {
            $table->dropIndex('idx_notas_alumno');
        });

        Schema::dropIfExists('auditoria_notas');
        Schema::dropIfExists('envios_correo');
        Schema::dropIfExists('plantillas_correo');
        Schema::dropIfExists('padre_alumno');
        Schema::dropIfExists('padres');
        Schema::dropIfExists('notas');
        Schema::dropIfExists('asignaciones');
        Schema::dropIfExists('categorias_evaluacion');
        Schema::dropIfExists('trimestres');
        Schema::dropIfExists('materias');
        Schema::dropIfExists('alumnos');
        Schema::dropIfExists('secciones');
        Schema::dropIfExists('profesores');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
