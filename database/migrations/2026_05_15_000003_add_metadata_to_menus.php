<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('descripcion', 200)->nullable()->after('nombre');
            $table->text('tablas_relacionadas')->nullable()->after('url');
        });

        $associations = [
            'dashboard' => ['descripcion' => 'Panel principal del sistema.', 'tablas_relacionadas' => 'secciones, alumnos, profesores, notas_alumnos'],
            'sections' => ['descripcion' => 'Mantenimiento de secciones y grados.', 'tablas_relacionadas' => 'secciones'],
            'students' => ['descripcion' => 'Mantenimiento de alumnos.', 'tablas_relacionadas' => 'alumnos'],
            'teachers' => ['descripcion' => 'Mantenimiento de docentes.', 'tablas_relacionadas' => 'profesores'],
            'subjects' => ['descripcion' => 'Mantenimiento de materias.', 'tablas_relacionadas' => 'materias'],
            'guardians' => ['descripcion' => 'Mantenimiento de familias y parentescos.', 'tablas_relacionadas' => 'padres, padre_alumno'],
            'gradebook' => ['descripcion' => 'Colector y configuracion de notas.', 'tablas_relacionadas' => 'categorias_evaluacion, notas_alumnos, conducta_alumnos, asignaciones, trimestres'],
            'reportcard' => ['descripcion' => 'Boletines y reportes academicos.', 'tablas_relacionadas' => 'notas_alumnos, conducta_alumnos, asignaciones, trimestres, alumnos'],
            'emails' => ['descripcion' => 'Envio y plantillas de correo.', 'tablas_relacionadas' => 'envios_correo, plantillas_correo, padres, alumnos'],
            'users' => ['descripcion' => 'Mantenimiento de usuarios.', 'tablas_relacionadas' => 'usuarios'],
            'profiles' => ['descripcion' => 'Perfiles y permisos de acceso.', 'tablas_relacionadas' => 'roles, rol_menu'],
            'config' => ['descripcion' => 'Resumen y parametros generales.', 'tablas_relacionadas' => 'menus, roles, usuarios'],
        ];

        foreach ($associations as $key => $data) {
            DB::table('menus')->where('clave', $key)->update($data);
        }

        $existingId = DB::table('menus')->where('clave', 'menus')->value('id');

        if (! $existingId) {
            $menuId = DB::table('menus')->insertGetId([
                'clave' => 'menus',
                'nombre' => 'Menus',
                'descripcion' => 'Mantenimiento del catalogo de menus del sistema.',
                'icono' => 'fas fa-bars',
                'url' => '/pad/menus',
                'tablas_relacionadas' => 'menus, rol_menu',
                'orden' => 12,
                'activo' => 1,
            ]);

            DB::table('menus')->where('clave', 'config')->update(['orden' => 13]);

            $adminRoleId = DB::table('roles')->where('nombre', 'admin')->value('id');

            if ($adminRoleId) {
                DB::table('rol_menu')->updateOrInsert(
                    ['rol_id' => $adminRoleId, 'menu_id' => $menuId],
                    ['rol_id' => $adminRoleId, 'menu_id' => $menuId]
                );
            }
        }
    }

    public function down(): void
    {
        $menuId = DB::table('menus')->where('clave', 'menus')->value('id');

        if ($menuId) {
            DB::table('rol_menu')->where('menu_id', $menuId)->delete();
            DB::table('menus')->where('id', $menuId)->delete();
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'tablas_relacionadas']);
        });
    }
};
