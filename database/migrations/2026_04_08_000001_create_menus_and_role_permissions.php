<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 50)->unique();
            $table->string('nombre', 100);
            $table->string('icono', 100)->nullable();
            $table->string('url', 150)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
        });

        Schema::create('rol_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->unique(['rol_id', 'menu_id'], 'uq_rol_menu');
        });

        DB::table('menus')->insert([
            ['id' => 1, 'clave' => 'dashboard', 'nombre' => 'Dashboard', 'icono' => 'fas fa-tachometer-alt', 'url' => '/pad/', 'orden' => 1, 'activo' => 1],
            ['id' => 2, 'clave' => 'sections', 'nombre' => 'Secciones', 'icono' => 'fas fa-school', 'url' => '/pad/secciones', 'orden' => 2, 'activo' => 1],
            ['id' => 3, 'clave' => 'students', 'nombre' => 'Alumnos', 'icono' => 'fas fa-user-graduate', 'url' => '/pad/alumnos', 'orden' => 3, 'activo' => 1],
            ['id' => 4, 'clave' => 'teachers', 'nombre' => 'Profesores', 'icono' => 'fas fa-chalkboard-teacher', 'url' => '/pad/profesores', 'orden' => 4, 'activo' => 1],
            ['id' => 5, 'clave' => 'subjects', 'nombre' => 'Materias', 'icono' => 'fas fa-book-open', 'url' => '/pad/materias', 'orden' => 5, 'activo' => 1],
            ['id' => 6, 'clave' => 'guardians', 'nombre' => 'Familias', 'icono' => 'fas fa-users', 'url' => '/pad/familias', 'orden' => 6, 'activo' => 1],
            ['id' => 7, 'clave' => 'gradebook', 'nombre' => 'Notas', 'icono' => 'fas fa-pencil-alt', 'url' => '/pad/notas', 'orden' => 7, 'activo' => 1],
            ['id' => 8, 'clave' => 'reportcard', 'nombre' => 'Boletines', 'icono' => 'fas fa-clipboard-list', 'url' => '/pad/report-card', 'orden' => 8, 'activo' => 1],
            ['id' => 9, 'clave' => 'emails', 'nombre' => 'Correos', 'icono' => 'fas fa-paper-plane', 'url' => '/pad/correos', 'orden' => 9, 'activo' => 1],
            ['id' => 10, 'clave' => 'users', 'nombre' => 'Usuarios', 'icono' => 'fas fa-user-shield', 'url' => '/pad/usuarios', 'orden' => 10, 'activo' => 1],
            ['id' => 11, 'clave' => 'profiles', 'nombre' => 'Perfiles', 'icono' => 'fas fa-user-lock', 'url' => '/pad/perfiles', 'orden' => 11, 'activo' => 1],
            ['id' => 12, 'clave' => 'config', 'nombre' => 'Configuracion', 'icono' => 'fas fa-cog', 'url' => '/pad/configuracion', 'orden' => 12, 'activo' => 1],
        ]);

        $roles = DB::table('roles')->pluck('id', 'nombre');
        $menus = DB::table('menus')->pluck('id', 'clave');

        $assignments = [
            'admin' => array_keys($menus->all()),
            'director' => ['dashboard', 'sections', 'students', 'teachers', 'subjects', 'guardians', 'reportcard', 'emails'],
            'secretaria' => ['dashboard', 'sections', 'students', 'teachers', 'subjects', 'guardians', 'gradebook', 'reportcard', 'emails'],
            'profesor' => ['dashboard', 'students', 'subjects', 'gradebook', 'reportcard'],
            'padre' => ['dashboard', 'reportcard', 'emails'],
        ];

        $rows = [];

        foreach ($assignments as $roleName => $menuKeys) {
            $roleId = $roles[$roleName] ?? null;

            if (! $roleId) {
                continue;
            }

            foreach ($menuKeys as $menuKey) {
                $menuId = $menus[$menuKey] ?? null;

                if ($menuId) {
                    $rows[] = ['rol_id' => $roleId, 'menu_id' => $menuId];
                }
            }
        }

        if ($rows !== []) {
            DB::table('rol_menu')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_menu');
        Schema::dropIfExists('menus');
    }
};
