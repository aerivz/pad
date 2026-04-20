<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'seccion_id' => ['required', 'exists:secciones,id'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
        ]);

        Student::create($data);

        return redirect('/pad/alumnos')->with('status', 'Alumno creado correctamente.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'seccion_id' => ['required', 'exists:secciones,id'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
        ]);

        $student->update($data);

        return redirect('/pad/alumnos')->with('status', 'Alumno actualizado correctamente.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->update(['activo' => false]);

        return redirect('/pad/alumnos')->with('status', 'Alumno desactivado correctamente.');
    }
}
