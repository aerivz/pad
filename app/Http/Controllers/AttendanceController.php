<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Support\AppUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function sync(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'fecha' => ['required', 'date'],
            'estado' => ['nullable', 'array'],
            'estado.*' => ['nullable', 'in:presente,ausente'],
            'tipo_justificante' => ['nullable', 'array'],
            'tipo_justificante.*' => ['nullable', 'in:sin_justificante,justificante'],
            'motivo' => ['nullable', 'array'],
            'motivo.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $section = Section::active()->findOrFail($data['seccion_id']);
        $this->assertSectionAccess($section);
        $studentIds = $this->studentIdsFor($section);
        $saved = 0;

        DB::transaction(function () use ($data, $section, $studentIds, &$saved): void {
            foreach (($data['estado'] ?? []) as $studentId => $state) {
                if (! $state || ! $studentIds->contains((int) $studentId)) {
                    continue;
                }

                $isJustified = $state === 'ausente'
                    && (($data['tipo_justificante'][$studentId] ?? 'sin_justificante') === 'justificante');
                $reason = trim((string) ($data['motivo'][$studentId] ?? ''));

                if ($isJustified && $reason === '') {
                    throw ValidationException::withMessages([
                        'motivo.'.$studentId => 'Ingresa motivo para cada ausencia justificada.',
                    ]);
                }

                StudentAttendance::query()->updateOrCreate(
                    ['alumno_id' => $studentId, 'fecha' => $data['fecha']],
                    [
                        'seccion_id' => $section->id,
                        'estado' => $isJustified ? 'justificado' : $state,
                        'justificante' => $isJustified ? $reason : null,
                        'registrado_por' => Auth::id(),
                    ]
                );
                $saved++;
            }
        });

        return redirect(AppUrl::route('attendance.index', [
            'anio_escolar' => $section->anio_escolar,
            'seccion_id' => $section->id,
            'fecha' => $data['fecha'],
        ]))->with('status', $saved.' asistencias actualizadas.');
    }

    public function mark(Request $request, Student $student): JsonResponse
    {
        $data = $request->validate([
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'fecha' => ['required', 'date'],
            'estado' => ['required', 'in:presente,ausente,justificado'],
            'justificante' => ['nullable', 'string', 'max:1000'],
        ]);

        $section = Section::active()->findOrFail($data['seccion_id']);
        $this->assertSectionAccess($section);
        abort_unless($student->activo && $student->seccion_id === $section->id, 422, 'El alumno no pertenece a esta seccion.');

        $reason = trim((string) ($data['justificante'] ?? ''));
        abort_if($data['estado'] === 'justificado' && $reason === '', 422, 'Ingresa motivo del justificante.');

        $attendance = StudentAttendance::query()->updateOrCreate(
            ['alumno_id' => $student->id, 'fecha' => $data['fecha']],
            [
                'seccion_id' => $section->id,
                'estado' => $data['estado'],
                'justificante' => $data['estado'] === 'justificado' ? $reason : null,
                'registrado_por' => Auth::id(),
            ]
        );

        return response()->json([
            'id' => $attendance->id,
            'estado' => $attendance->estado,
            'justificante' => $attendance->justificante,
        ]);
    }

    public function restore(Request $request, Student $student): JsonResponse
    {
        $data = $request->validate([
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'fecha' => ['required', 'date'],
            'estado_anterior' => ['nullable', 'in:presente,ausente,justificado'],
            'justificante_anterior' => ['nullable', 'string', 'max:1000'],
        ]);

        $section = Section::active()->findOrFail($data['seccion_id']);
        $this->assertSectionAccess($section);
        abort_unless($student->activo && $student->seccion_id === $section->id, 422, 'El alumno no pertenece a esta seccion.');

        $attendance = StudentAttendance::query()
            ->where('alumno_id', $student->id)
            ->where('fecha', $data['fecha'])
            ->first();

        if (! $data['estado_anterior']) {
            $attendance?->delete();
        } else {
            StudentAttendance::query()->updateOrCreate(
                ['alumno_id' => $student->id, 'fecha' => $data['fecha']],
                [
                    'seccion_id' => $section->id,
                    'estado' => $data['estado_anterior'],
                    'justificante' => $data['estado_anterior'] === 'justificado'
                        ? trim((string) ($data['justificante_anterior'] ?? ''))
                        : null,
                    'registrado_por' => Auth::id(),
                ]
            );
        }

        return response()->json(['ok' => true]);
    }

    private function assertSectionAccess(Section $section): void
    {
        $user = Auth::user();

        if (! $user?->isProfessor()) {
            return;
        }

        $teacherId = $user->teacher()->value('id');
        abort_unless($teacherId && (int) $section->titular_profesor_id === (int) $teacherId, 403);
    }

    private function studentIdsFor(Section $section)
    {
        return Student::active()->where('seccion_id', $section->id)->pluck('id');
    }
}
