<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_cannot_mutate_students_with_view_menu_access(): void
    {
        [$user, $menuId] = $this->userWithMenu('director', 'students');
        DB::table('rol_menu')->insert(['rol_id' => $user->rol_id, 'menu_id' => $menuId]);
        $sectionId = DB::table('secciones')->insertGetId([
            'nombre' => 'A',
            'grado' => '1',
            'anio_escolar' => 2026,
            'activo' => true,
        ]);

        $response = $this->actingAs($user)->post('/alumnos', [
            'seccion_id' => $sectionId,
            'nombres' => 'No',
            'apellidos' => 'Autorizado',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('alumnos', ['apellidos' => 'Autorizado']);
    }

    public function test_parent_cannot_administer_email_even_if_menu_is_assigned(): void
    {
        [$user, $menuId] = $this->userWithMenu('padre', 'emails');
        DB::table('rol_menu')->insert(['rol_id' => $user->rol_id, 'menu_id' => $menuId]);

        $this->actingAs($user)->get('/correos')->assertForbidden();
    }

    private function userWithMenu(string $roleName, string $menuKey): array
    {
        $roleId = DB::table('roles')->insertGetId([
            'nombre' => $roleName,
            'descripcion' => 'Prueba',
            'activo' => true,
        ]);
        $menuId = DB::table('menus')->where('clave', $menuKey)->value('id');
        $userId = DB::table('usuarios')->insertGetId([
            'rol_id' => $roleId,
            'nombre_usuario' => $roleName.'.prueba',
            'email' => $roleName.'@example.test',
            'password_hash' => Hash::make('Clave-Segura9'),
            'must_change_password' => false,
            'nombres' => ucfirst($roleName),
            'apellidos' => 'Prueba',
            'activo' => true,
            'created_at' => now(),
        ]);

        return [User::query()->findOrFail($userId), $menuId];
    }
}
