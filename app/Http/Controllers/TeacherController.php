<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:profesores,email'],
            'especialidad' => ['nullable', 'string', 'max:150'],
        ]);

        Teacher::create($data);

        return redirect('/pad/profesores')->with('status', 'Profesor creado correctamente.');
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('profesores', 'email')->ignore($teacher->id)],
            'especialidad' => ['nullable', 'string', 'max:150'],
        ]);

        $teacher->update($data);

        return redirect('/pad/profesores')->with('status', 'Profesor actualizado correctamente.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->update(['activo' => false]);

        return redirect('/pad/profesores')->with('status', 'Profesor desactivado correctamente.');
    }
}
