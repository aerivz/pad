<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->boolean('must_change_password')->default(false)->after('password_hash');
        });

        // Existing hashes cannot be classified safely without the plaintext password.
        // Force a one-time change for every existing account.
        DB::table('usuarios')->update(['must_change_password' => true]);

        Schema::table('padres', function (Blueprint $table): void {
            $table->foreignId('usuario_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('usuarios')
                ->nullOnDelete();
        });

        $parentRoleId = DB::table('roles')->where('nombre', 'padre')->value('id');
        $directorRoleId = DB::table('roles')->where('nombre', 'director')->value('id');
        $emailMenuId = DB::table('menus')->where('clave', 'emails')->value('id');

        if ($emailMenuId) {
            DB::table('rol_menu')
                ->where('menu_id', $emailMenuId)
                ->whereIn('rol_id', array_filter([$parentRoleId, $directorRoleId]))
                ->delete();
        }

        $guardians = DB::table('padres')->whereNull('usuario_id')->get(['id', 'email_principal']);

        foreach ($guardians as $guardian) {
            $matchingUsers = DB::table('usuarios')
                ->where('rol_id', $parentRoleId)
                ->where('email', $guardian->email_principal)
                ->pluck('id');

            if ($matchingUsers->count() === 1) {
                DB::table('padres')->where('id', $guardian->id)->update(['usuario_id' => $matchingUsers->first()]);
            }
        }
    }

    public function down(): void
    {
        $emailMenuId = DB::table('menus')->where('clave', 'emails')->value('id');
        $roleIds = DB::table('roles')->whereIn('nombre', ['director', 'padre'])->pluck('id');

        foreach ($roleIds as $roleId) {
            if ($emailMenuId) {
                DB::table('rol_menu')->insertOrIgnore([
                    'rol_id' => $roleId,
                    'menu_id' => $emailMenuId,
                ]);
            }
        }

        Schema::table('padres', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('usuario_id');
        });

        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dropColumn('must_change_password');
        });
    }
};
