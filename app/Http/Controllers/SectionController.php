<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'grado' => ['required', 'string', 'max:50'],
            'anio_escolar' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        Section::create($data);

        return redirect('/pad/secciones')->with('status', 'Sección creada correctamente.');
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'grado' => ['required', 'string', 'max:50'],
            'anio_escolar' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $section->update($data);

        return redirect('/pad/secciones')->with('status', 'Sección actualizada correctamente.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        $section->update(['activo' => false]);

        return redirect('/pad/secciones')->with('status', 'Sección desactivada correctamente.');
    }
}
