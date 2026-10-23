<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAssignment($request);
        $this->ensureUniqueAssignment($data);

        DB::transaction(function () use ($data): void {
            $assignment = Assignment::create(collect($data)->except('alumno_ids')->all() + ['activo' => true]);
            $assignment->students()->sync($data['alumno_ids'] ?? []);
        });

        return redirect(AppUrl::route('assignments.index'))
            ->with('status', 'Asignacion creada correctamente.');
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $data = $this->validateAssignment($request);
        $this->ensureUniqueAssignment($data, $assignment->id);

        DB::transaction(function () use ($assignment, $data): void {
            $assignment->update(collect($data)->except('alumno_ids')->all());
            $assignment->students()->sync($data['alumno_ids'] ?? []);
        });

        return redirect(AppUrl::route('assignments.index'))
            ->with('status', 'Asignacion actualizada correctamente.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $assignment->update(['activo' => false]);

        return redirect(AppUrl::route('assignments.index'))
            ->with('status', 'Asignacion desactivada correctamente.');
    }

    private function validateAssignment(Request $request): array
    {
        $data = $request->validate([
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'materia_id' => ['required', 'integer', Rule::exists('materias', 'id')->where('activo', true)],
            'profesor_id' => ['required', 'integer', 'exists:profesores,id'],
            'anio_escolar' => ['required', 'integer', 'min:2000', 'max:2100'],
            'alumno_ids' => ['nullable', 'array'],
            'alumno_ids.*' => ['integer', Rule::exists('alumnos', 'id')->where('activo', true)],
        ]);

        $isElective = DB::table('materias')->where('id', $data['materia_id'])->where('es_electiva', true)->exists();

        if ($isElective && empty($data['alumno_ids'])) {
            throw ValidationException::withMessages([
                'alumno_ids' => 'Selecciona al menos un alumno para la materia electiva.',
            ]);
        }

        if (! $isElective) {
            $data['alumno_ids'] = [];
        }

        $invalidYear = DB::table('alumnos as a')
            ->join('secciones as s', 's.id', '=', 'a.seccion_id')
            ->whereIn('a.id', $data['alumno_ids'])
            ->where('s.anio_escolar', '!=', $data['anio_escolar'])
            ->exists();

        if ($invalidYear) {
            throw ValidationException::withMessages([
                'alumno_ids' => 'Todos los alumnos electivos deben pertenecer al año lectivo seleccionado.',
            ]);
        }

        return $data;
    }

    private function ensureUniqueAssignment(array $data, ?int $ignoreId = null): void
    {
        $baseQuery = Assignment::active()
            ->where('seccion_id', $data['seccion_id'])
            ->where('materia_id', $data['materia_id'])
            ->where('anio_escolar', $data['anio_escolar'])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId));

        $sameTeacherExists = (clone $baseQuery)
            ->where('profesor_id', $data['profesor_id'])
            ->exists();

        if ($sameTeacherExists) {
            throw ValidationException::withMessages([
                'profesor_id' => 'Ese profesor ya esta asignado a la misma materia y seccion en ese ano lectivo.',
            ]);
        }

        if ((clone $baseQuery)->exists()) {
            throw ValidationException::withMessages([
                'materia_id' => 'Ya existe una asignacion activa para esa materia y seccion en ese ano lectivo.',
            ]);
        }
    }
}
