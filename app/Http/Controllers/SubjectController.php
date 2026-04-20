<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
        ]);

        Subject::create($data);

        return redirect('/pad/materias')->with('status', 'Materia creada correctamente.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
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
