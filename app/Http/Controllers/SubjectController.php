<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'plantilla_colector' => [
                'nullable',
                'string',
                Rule::exists('plantillas_colector', 'codigo')->where(fn ($query) => $query->where('activo', true)),
            ],
        ]);

        Subject::create($data);

        return redirect('/pad/materias')->with('status', 'Materia creada correctamente.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'plantilla_colector' => [
                'nullable',
                'string',
                Rule::exists('plantillas_colector', 'codigo')->where(fn ($query) => $query->where('activo', true)),
            ],
        ]);

        $subject->update($data);

        return redirect('/pad/materias')->with('status', 'Materia actualizada correctamente.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->update(['activo' => false]);

        return redirect('/pad/materias')->with('status', 'Materia desactivada correctamente.');
    }
}
