<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateNote($request);

        $existingInactive = Note::query()
            ->where('alumno_id', $data['alumno_id'])
            ->where('asignacion_id', $data['asignacion_id'])
            ->where('trimestre_id', $data['trimestre_id'])
            ->where('categoria_id', $data['categoria_id'])
            ->where('activo', false)
            ->first();

        if ($existingInactive) {
            $previousValue = $existingInactive->valor;

            $existingInactive->update([
                'valor' => $data['valor'],
                'activo' => true,
            ]);

            $this->registerAudit($existingInactive->id, $previousValue, $data['valor'], 'UPDATE');

            return redirect('/pad/notas')->with('status', 'Nota reactivada y actualizada correctamente.');
        }

        $note = Note::create([
            ...$data,
            'activo' => true,
        ]);

        $this->registerAudit($note->id, null, $data['valor'], 'INSERT');

        return redirect('/pad/notas')->with('status', 'Nota creada correctamente.');
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $data = $this->validateNote($request, $note);
        $previousValue = $note->valor;

        $note->update($data);

        $this->registerAudit($note->id, $previousValue, $data['valor'], 'UPDATE');

        return redirect('/pad/notas')->with('status', 'Nota actualizada correctamente.');
    }

    public function destroy(Note $note): RedirectResponse
    {
        $previousValue = $note->valor;

        $note->update(['activo' => false]);

        $this->registerAudit($note->id, $previousValue, null, 'DELETE');

        return redirect('/pad/notas')->with('status', 'Nota desactivada correctamente.');
    }

    private function validateNote(Request $request, ?Note $note = null): array
    {
        return $request->validate([
            'alumno_id' => ['required', 'integer', 'exists:alumnos,id'],
            'asignacion_id' => ['required', 'integer', 'exists:asignaciones,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'categoria_id' => [
                'required',
                'integer',
                'exists:categorias_evaluacion,id',
                Rule::unique('notas')->where(function ($query) use ($request) {
                    return $query
                        ->where('alumno_id', $request->input('alumno_id'))
                        ->where('asignacion_id', $request->input('asignacion_id'))
                        ->where('trimestre_id', $request->input('trimestre_id'))
                        ->where('activo', true);
                })->ignore($note?->id),
            ],
            'valor' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function registerAudit(int $noteId, ?string $previousValue, ?string $newValue, string $action): void
    {
        DB::table('auditoria_notas')->insert([
            'nota_id' => $noteId,
            'usuario_id' => 1,
            'valor_anterior' => $previousValue,
            'valor_nuevo' => $newValue,
            'accion' => $action,
        ]);
    }
}
