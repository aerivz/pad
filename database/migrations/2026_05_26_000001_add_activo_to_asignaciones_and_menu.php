<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('anio_escolar');
        });

        DB::table('asignaciones')->update(['activo' => true]);

        $menuId = DB::table('menus')->where('clave', 'assignments')->value('id');

        if (! $menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'clave' => 'assignments',
                'nombre' => 'Asignaciones',
                'descripcion' => 'Relacion de materias, profesores y secciones por año lectivo.',
                'icono' => 'fas fa-project-diagram',
                'url' => '/asignaciones',
                'tablas_relacionadas' => 'asignaciones, materias, profesores, secciones',
                'orden' => 8,
                'activo' => 1,
            ]);

            DB::table('menus')
                ->whereIn('clave', ['reportcard', 'emails', 'users', 'profiles', 'backups', 'config', 'menus'])
                ->increment('orden');
        }

        $roleIds = DB::table('roles')
            ->whereIn('nombre', ['admin', 'secretaria'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            $exists = DB::table('rol_menu')
                ->where('rol_id', $roleId)
                ->where('menu_id', $menuId)
                ->exists();

            if (! $exists) {
                DB::table('rol_menu')->insert([
                    'rol_id' => $roleId,
                    'menu_id' => $menuId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $menuId = DB::table('menus')->where('clave', 'assignments')->value('id');

        if ($menuId) {
            DB::table('rol_menu')->where('menu_id', $menuId)->delete();
            DB::table('menus')->where('id', $menuId)->delete();
        }

        DB::table('menus')
            ->whereIn('clave', ['reportcard', 'emails', 'users', 'profiles', 'backups', 'config', 'menus'])
            ->decrement('orden');

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }
};
