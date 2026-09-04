<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('seccion_id')->constrained('secciones');
            $table->date('fecha');
            $table->enum('estado', ['presente', 'ausente', 'justificado']);
            $table->text('justificante')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->unique(['alumno_id', 'fecha'], 'uq_asistencia_alumno_fecha');
            $table->index(['seccion_id', 'fecha'], 'idx_asistencia_seccion_fecha');
        });

        $parentId = DB::table('menus')->where('clave', 'evaluation')->value('id');
        $menuId = DB::table('menus')->where('clave', 'attendance')->value('id');

        if (! $menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'parent_id' => $parentId,
                'clave' => 'attendance',
                'nombre' => 'Asistencia',
                'descripcion' => 'Control diario de asistencia por seccion.',
                'icono' => 'fas fa-user-check',
                'url' => '/pad/asistencia',
                'tablas_relacionadas' => 'asistencias_alumnos, alumnos, secciones, usuarios',
                'orden' => 4,
                'activo' => true,
            ]);
        }

        $roleIds = DB::table('roles')
            ->whereIn('nombre', ['admin', 'director', 'secretaria', 'profesor'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('rol_menu')->updateOrInsert(
                ['rol_id' => $roleId, 'menu_id' => $menuId],
                ['rol_id' => $roleId, 'menu_id' => $menuId]
            );
        }
    }

    public function down(): void
    {
        $menuId = DB::table('menus')->where('clave', 'attendance')->value('id');

        if ($menuId) {
            DB::table('rol_menu')->where('menu_id', $menuId)->delete();
            DB::table('menus')->where('id', $menuId)->delete();
        }

        Schema::dropIfExists('asistencias_alumnos');
    }
};
