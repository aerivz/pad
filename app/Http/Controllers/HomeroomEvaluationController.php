<?php

namespace App\Http\Controllers;

use App\Models\HomeroomEvaluation;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeroomEvaluationController extends Controller
{
    public function sync(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'periodo' => ['nullable', 'string', 'max:40'],
            'evaluations' => ['nullable', 'array'],
            'evaluations.*.conducta' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'evaluations.*.comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $teacherId = DB::table('profesores')
            ->where('usuario_id', $request->user()->id)
            ->where('activo', true)
            ->value('id');
        $isHolder = $teacherId && DB::table('secciones')
            ->where('id', $data['seccion_id'])
            ->where('titular_profesor_id', $teacherId)
            ->where('activo', true)
            ->exists();

        abort_unless($isHolder, 403);

        $studentIds = DB::table('alumnos')
            ->where('seccion_id', $data['seccion_id'])
            ->where('activo', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::transaction(function () use ($data, $studentIds): void {
            foreach (($data['evaluations'] ?? []) as $studentId => $values) {
                $studentId = (int) $studentId;

                if (! in_array($studentId, $studentIds, true)) {
                    continue;
                }

                $conduct = $values['conducta'] ?? null;
                $comment = trim((string) ($values['comentario'] ?? ''));
                $hasValues = ($conduct !== null && $conduct !== '') || $comment !== '';

                HomeroomEvaluation::query()->updateOrCreate(
                    [
                        'seccion_id' => $data['seccion_id'],
                        'trimestre_id' => $data['trimestre_id'],
                        'alumno_id' => $studentId,
                    ],
                    [
                        'conducta' => $conduct === null || $conduct === '' ? null : $conduct,
                        'comentario' => $comment === '' ? null : $comment,
                        'activo' => $hasValues,
                    ]
                );
            }
        });

        $query = http_build_query([
            'anio_escolar' => DB::table('secciones')->where('id', $data['seccion_id'])->value('anio_escolar'),
            'seccion_id' => $data['seccion_id'],
            'periodo' => $data['periodo'] ?? 'trimester:'.$data['trimestre_id'],
        ]);

        return redirect(AppUrl::route('gradebook.index').'?'.$query)
            ->with('status', 'Conducta y comentarios guardados correctamente.');
    }
}
