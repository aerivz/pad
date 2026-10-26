<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menuId = DB::table('menus')->where('clave', 'error_logs')->value('id');

        if (! $menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'clave' => 'error_logs',
                'nombre' => 'Errores del sistema',
                'descripcion' => 'Consulta administrativa de errores por rango de fechas.',
                'icono' => 'fas fa-exclamation-triangle',
                'url' => '/pad/errores',
                'tablas_relacionadas' => null,
                'orden' => ((int) DB::table('menus')->max('orden')) + 1,
                'activo' => true,
            ]);
        }

        $adminRoleId = DB::table('roles')->where('nombre', 'admin')->value('id');

        if ($adminRoleId && $menuId) {
            DB::table('rol_menu')->updateOrInsert(
                ['rol_id' => $adminRoleId, 'menu_id' => $menuId],
                ['rol_id' => $adminRoleId, 'menu_id' => $menuId]
            );
        }
    }

    public function down(): void
    {
        $menuId = DB::table('menus')->where('clave', 'error_logs')->value('id');

        if ($menuId) {
            DB::table('rol_menu')->where('menu_id', $menuId)->delete();
            DB::table('menus')->where('id', $menuId)->delete();
        }
    }
};
