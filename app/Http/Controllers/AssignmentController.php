<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAssignment($request);
        $this->ensureUniqueAssignment($data);

        Assignment::create($data + ['activo' => true]);

        return redirect(AppUrl::route('assignments.index'))
            ->with('status', 'Asignacion creada correctamente.');
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $data = $this->validateAssignment($request);
        $this->ensureUniqueAssignment($data, $assignment->id);

        $assignment->update($data);

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
        return $request->validate([
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'materia_id' => ['required', 'integer', 'exists:materias,id'],
            'profesor_id' => ['required', 'integer', 'exists:profesores,id'],
            'anio_escolar' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);
    }

    private function ensureUniqueAssignment(array $data, ?int $ignoreId = null): void
    {
        $exists = Assignment::active()
            ->where('seccion_id', $data['seccion_id'])
            ->where('materia_id', $data['materia_id'])
            ->where('anio_escolar', $data['anio_escolar'])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'materia_id' => 'Ya existe una asignacion activa para esa materia, seccion y año lectivo.',
            ]);
        }
    }
}
