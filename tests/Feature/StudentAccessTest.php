<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\StudentAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_only_receives_linked_students(): void
    {
        $parentRoleId = DB::table('roles')->insertGetId([
            'nombre' => 'padre',
            'descripcion' => 'Consulta de notas',
            'activo' => true,
        ]);
        $userId = DB::table('usuarios')->insertGetId([
            'rol_id' => $parentRoleId,
            'nombre_usuario' => 'padre.prueba',
            'email' => 'padre@example.test',
            'password_hash' => Hash::make('Clave-Segura9'),
            'must_change_password' => false,
            'nombres' => 'Padre',
            'apellidos' => 'Prueba',
            'activo' => true,
            'created_at' => now(),
        ]);
        $sectionId = DB::table('secciones')->insertGetId([
            'nombre' => 'A',
            'grado' => '1',
            'anio_escolar' => 2026,
            'activo' => true,
        ]);
        $allowedStudentId = DB::table('alumnos')->insertGetId([
            'seccion_id' => $sectionId,
            'nombres' => 'Alumno',
            'apellidos' => 'Permitido',
            'activo' => true,
        ]);
        $otherStudentId = DB::table('alumnos')->insertGetId([
            'seccion_id' => $sectionId,
            'nombres' => 'Alumno',
            'apellidos' => 'Ajeno',
            'activo' => true,
        ]);
        $guardianId = DB::table('padres')->insertGetId([
            'usuario_id' => $userId,
            'nombres' => 'Padre',
            'apellidos' => 'Prueba',
            'email_principal' => 'padre@example.test',
            'activo' => true,
        ]);
        DB::table('padre_alumno')->insert([
            'padre_id' => $guardianId,
            'alumno_id' => $allowedStudentId,
            'parentesco' => 'Padre',
        ]);

        $user = User::query()->findOrFail($userId);
        $access = app(StudentAccess::class);

        $this->assertTrue($access->allowsStudent($user, $allowedStudentId));
        $this->assertFalse($access->allowsStudent($user, $otherStudentId));
    }
}
