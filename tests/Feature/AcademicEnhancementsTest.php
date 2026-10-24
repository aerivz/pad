<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AcademicEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_elective_assignment_accepts_students_from_multiple_sections(): void
    {
        [$user, $menuId] = $this->userWithMenu('admin', 'assignments');
        DB::table('rol_menu')->insert(['rol_id' => $user->rol_id, 'menu_id' => $menuId]);
        $sectionA = $this->section('Segundo', 'A');
        $sectionB = $this->section('Noveno', 'A');
        $studentA = $this->student($sectionA, 'Ana');
        $studentB = $this->student($sectionB, 'Luis');
        $subjectId = DB::table('materias')->insertGetId([
            'nombre' => 'Artes electiva',
            'activo' => true,
            'es_electiva' => true,
        ]);
        $teacherId = DB::table('profesores')->insertGetId([
            'nombres' => 'Docente',
            'apellidos' => 'Artes',
            'email' => 'docente-artes@example.test',
            'activo' => true,
        ]);

        $this->actingAs($user)->post('/asignaciones', [
            'seccion_id' => $sectionA,
            'materia_id' => $subjectId,
            'profesor_id' => $teacherId,
            'anio_escolar' => 2026,
            'alumno_ids' => [$studentA, $studentB],
        ])->assertSessionHasNoErrors();

        $assignmentId = DB::table('asignaciones')->value('id');
        $this->assertDatabaseHas('asignacion_alumno', ['asignacion_id' => $assignmentId, 'alumno_id' => $studentA]);
        $this->assertDatabaseHas('asignacion_alumno', ['asignacion_id' => $assignmentId, 'alumno_id' => $studentB]);
    }

    public function test_only_section_holder_can_save_conduct_and_comment(): void
    {
        [$user, $menuId] = $this->userWithMenu('profesor', 'gradebook');
        DB::table('rol_menu')->insert(['rol_id' => $user->rol_id, 'menu_id' => $menuId]);
        $teacherId = DB::table('profesores')->insertGetId([
            'usuario_id' => $user->id,
            'nombres' => 'Maestra',
            'apellidos' => 'Titular',
            'email' => 'maestra-titular@example.test',
            'activo' => true,
        ]);
        $sectionId = $this->section('Segundo', 'A', $teacherId);
        $studentId = $this->student($sectionId, 'Ana');
        $trimesterId = DB::table('trimestres')->insertGetId(['nombre' => 'Trimestre 1', 'numero' => 1]);

        $this->actingAs($user)->post('/notas/titular', [
            'seccion_id' => $sectionId,
            'trimestre_id' => $trimesterId,
            'periodo' => 'trimester:'.$trimesterId,
            'evaluations' => [
                $studentId => ['conducta' => 95, 'comentario' => 'Excelente participación.'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('evaluaciones_titular', [
            'seccion_id' => $sectionId,
            'trimestre_id' => $trimesterId,
            'alumno_id' => $studentId,
            'conducta' => 95,
            'comentario' => 'Excelente participación.',
        ]);

        $otherUserId = DB::table('usuarios')->insertGetId([
            'rol_id' => $user->rol_id,
            'nombre_usuario' => 'profesor.no-titular',
            'email' => 'profesor-no-titular@example.test',
            'password_hash' => Hash::make('Clave-Segura9'),
            'must_change_password' => false,
            'nombres' => 'Profesor',
            'apellidos' => 'No titular',
            'activo' => true,
            'created_at' => now(),
        ]);
        $otherUser = User::query()->findOrFail($otherUserId);
        DB::table('profesores')->insert([
            'usuario_id' => $otherUser->id,
            'nombres' => 'Profesor',
            'apellidos' => 'No titular',
            'email' => 'otro-profesor@example.test',
            'activo' => true,
        ]);

        $this->actingAs($otherUser)->post('/notas/titular', [
            'seccion_id' => $sectionId,
            'trimestre_id' => $trimesterId,
            'evaluations' => [$studentId => ['conducta' => 10]],
        ])->assertForbidden();
    }

    public function test_admin_can_configure_ordered_trimester_start_dates(): void
    {
        [$user, $menuId] = $this->userWithMenu('admin', 'config');
        DB::table('rol_menu')->insert(['rol_id' => $user->rol_id, 'menu_id' => $menuId]);
        DB::table('trimestres')->delete();
        $trimesters = collect(range(1, 4))->mapWithKeys(function (int $number): array {
            $id = DB::table('trimestres')->insertGetId([
                'nombre' => 'Trimestre '.$number,
                'numero' => $number,
            ]);

            return [$id => sprintf('2026-%02d-01', (($number - 1) * 3) + 1)];
        });

        $this->actingAs($user)->put('/configuracion', [
            'report_academic_year' => '2025 - 2026',
            'trimester_starts' => $trimesters->all(),
            'mail_mailer' => 'log',
            'mail_rate_limit_per_minute' => 30,
        ])->assertSessionHasNoErrors();

        foreach ($trimesters as $id => $startDate) {
            $this->assertDatabaseHas('trimestres', ['id' => $id, 'fecha_inicio' => $startDate]);
        }

        $invalidStarts = $trimesters->all();
        $secondTrimesterId = $trimesters->keys()[1];
        $invalidStarts[$secondTrimesterId] = '2025-12-01';

        $this->actingAs($user)->put('/configuracion', [
            'trimester_starts' => $invalidStarts,
            'mail_mailer' => 'log',
            'mail_rate_limit_per_minute' => 30,
        ])->assertSessionHasErrors('trimester_starts.'.$secondTrimesterId);

        $this->assertDatabaseHas('trimestres', [
            'id' => $secondTrimesterId,
            'fecha_inicio' => $trimesters[$secondTrimesterId],
        ]);
    }

    public function test_attendance_summary_uses_automatic_trimester_end_date(): void
    {
        [$user, $menuId] = $this->userWithMenu('admin', 'attendance');
        DB::table('rol_menu')->insert(['rol_id' => $user->rol_id, 'menu_id' => $menuId]);
        DB::table('trimestres')->delete();
        $firstTrimesterId = DB::table('trimestres')->insertGetId([
            'nombre' => 'Primer trimestre',
            'numero' => 1,
            'fecha_inicio' => '2026-01-10',
        ]);
        DB::table('trimestres')->insert([
            'nombre' => 'Segundo trimestre',
            'numero' => 2,
            'fecha_inicio' => '2026-04-01',
        ]);
        $sectionId = $this->section('Segundo', 'A');
        $studentId = $this->student($sectionId, 'Ana');
        DB::table('asistencias_alumnos')->insert([
            [
                'alumno_id' => $studentId,
                'seccion_id' => $sectionId,
                'fecha' => '2026-03-30',
                'estado' => 'presente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'alumno_id' => $studentId,
                'seccion_id' => $sectionId,
                'fecha' => '2026-04-01',
                'estado' => 'ausente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user)->get('/asistencia?anio_escolar=2026&seccion_id='.$sectionId.'&fecha=2026-03-30&trimestre_id='.$firstTrimesterId);

        $response->assertOk()
            ->assertViewHas('attendanceTrimesterEnd', '2026-03-31')
            ->assertViewHas('attendanceTrimesterSummary', fn (array $summary): bool => $summary['presentes'] === 1 && $summary['ausentes'] === 0 && $summary['porcentaje'] === 100.0);
    }

    private function section(string $grade, string $name, ?int $teacherId = null): int
    {
        return DB::table('secciones')->insertGetId([
            'nombre' => $name,
            'grado' => $grade,
            'anio_escolar' => 2026,
            'titular_profesor_id' => $teacherId,
            'activo' => true,
        ]);
    }

    private function student(int $sectionId, string $name): int
    {
        return DB::table('alumnos')->insertGetId([
            'seccion_id' => $sectionId,
            'nombres' => $name,
            'apellidos' => 'Prueba',
            'activo' => true,
        ]);
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
            'nombre_usuario' => $roleName.'.academico',
            'email' => $roleName.'-academico@example.test',
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
