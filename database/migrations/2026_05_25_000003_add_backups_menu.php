<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menuId = DB::table('menus')->where('clave', 'backups')->value('id');

        if (! $menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'clave' => 'backups',
                'nombre' => 'Backups',
                'descripcion' => 'Respaldos del sistema en segundo plano.',
                'icono' => 'fas fa-file-archive',
                'url' => '/pad/backups',
                'tablas_relacionadas' => 'backups_sistema, jobs, failed_jobs',
                'orden' => 13,
                'activo' => 1,
            ]);

            DB::table('menus')->where('clave', 'config')->update(['orden' => 14]);
        }

        $adminRoleId = DB::table('roles')->where('nombre', 'admin')->value('id');
        $exists = DB::table('rol_menu')
            ->where('rol_id', $adminRoleId)
            ->where('menu_id', $menuId)
            ->exists();

        if ($adminRoleId && $menuId && ! $exists) {
            DB::table('rol_menu')->insert([
                'rol_id' => $adminRoleId,
                'menu_id' => $menuId,
            ]);
        }
    }

    public function down(): void
    {
        $menuId = DB::table('menus')->where('clave', 'backups')->value('id');

        if ($menuId) {
            DB::table('rol_menu')->where('menu_id', $menuId)->delete();
            DB::table('menus')->where('id', $menuId)->delete();
        }

        DB::table('menus')->where('clave', 'config')->update(['orden' => 13]);
    }
};
