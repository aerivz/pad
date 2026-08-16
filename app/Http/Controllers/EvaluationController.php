<?php

namespace App\Http\Controllers;

use App\Models\CollectorCategory;
use App\Models\StudentCategoryGrade;
use App\Models\StudentConduct;
use App\Models\StudentPeriodExam;
use App\Models\User;
use App\Services\GradeCollectorImportService;
use App\Services\GradeCollectorService;
use App\Support\CollectorTemplateCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvaluationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);
        $this->guardAssignmentEditAccess((int) $data['asignacion_id']);
        $this->guardCategoryPercentage($data['asignacion_id'], $data['trimestre_id'], (float) $data['porcentaje']);

        CollectorCategory::query()->create([
            ...$data,
            'cantidad_notas' => $this->gradeCollectorService()->quantityForType($data['tipo_calculo']),
            'activo' => true,
        ]);

        return redirect($this->gradebookRedirect($data))
            ->with('status', 'Categoria creada.');
    }

    public function update(Request $request, CollectorCategory $evaluation): RedirectResponse
    {
        $data = $this->validateCategory($request, $evaluation);
        $this->guardAssignmentEditAccess((int) $data['asignacion_id']);
        $this->guardCategoryPercentage($data['asignacion_id'], $data['trimestre_id'], (float) $data['porcentaje'], $evaluation->id);

        $evaluation->update([
            ...$data,
            'cantidad_notas' => $this->gradeCollectorService()->quantityForType($data['tipo_calculo']),
        ]);

        return redirect($this->gradebookRedirect($data))
            ->with('status', 'Categoria actualizada.');
    }

    public function destroy(CollectorCategory $evaluation): RedirectResponse
    {
        $this->guardAssignmentEditAccess((int) $evaluation->asignacion_id);

        DB::transaction(function () use ($evaluation): void {
            $evaluation->update(['activo' => false]);

            StudentCategoryGrade::query()
                ->where('categoria_id', $evaluation->id)
                ->update(['activo' => false]);
        });

        return redirect($this->gradebookRedirect([
            'asignacion_id' => $evaluation->asignacion_id,
            'trimestre_id' => $evaluation->trimestre_id,
        ]))->with('status', 'Categoria desactivada.');
    }

    public function syncScores(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignaciones,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'periodo' => ['nullable', 'string', 'max:40'],
            'grades' => ['nullable', 'array'],
            'grades.*' => ['array'],
            'grades.*.*' => ['array'],
            'grades.*.*.nota_1' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.*.nota_2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.*.nota_3' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.*.nota_4' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'conduct' => ['nullable', 'array'],
            'conduct.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'period_exam' => ['nullable', 'array'],
            'period_exam.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $this->guardAssignmentEditAccess((int) $data['asignacion_id']);

        $periodExamType = $this->resolvePeriodExamType($data);

        $categories = CollectorCategory::query()
            ->where('asignacion_id', $data['asignacion_id'])
            ->where('trimestre_id', $data['trimestre_id'])
            ->where('activo', true)
            ->get()
            ->keyBy('id');

        DB::transaction(function () use ($data, $categories, $periodExamType): void {
            foreach (($data['grades'] ?? []) as $studentId => $studentCategories) {
                foreach ($studentCategories as $categoryId => $notes) {
                    $category = $categories->get((int) $categoryId);

                    if (! $category) {
                        continue;
                    }

                    $payload = $this->normalizeGradePayload($category, $notes);
                    $grade = StudentCategoryGrade::query()
                        ->where('categoria_id', $category->id)
                        ->where('alumno_id', $studentId)
                        ->first();

                    $hasAnyScore = collect($payload)->only(['nota_1', 'nota_2', 'nota_3', 'nota_4'])->contains(fn ($value) => $value !== null);

                    if ($grade) {
                        $grade->update([
                            ...$payload,
                            'activo' => $hasAnyScore,
                        ]);
                    } elseif ($hasAnyScore) {
                        StudentCategoryGrade::query()->create([
                            'categoria_id' => $category->id,
                            'alumno_id' => $studentId,
                            ...$payload,
                            'activo' => true,
                        ]);
                    }
                }
            }

            foreach (($data['conduct'] ?? []) as $studentId => $value) {
                $payload = [
                    'valor' => $this->gradeCollectorService()->nullableNumber($value),
                    'activo' => $value !== null && $value !== '',
                ];

                $conduct = StudentConduct::query()
                    ->where('asignacion_id', $data['asignacion_id'])
                    ->where('trimestre_id', $data['trimestre_id'])
                    ->where('alumno_id', $studentId)
                    ->first();

                if ($conduct) {
                    $conduct->update($payload);
                } elseif ($payload['activo']) {
                    StudentConduct::query()->create([
                        'asignacion_id' => $data['asignacion_id'],
                        'trimestre_id' => $data['trimestre_id'],
                        'alumno_id' => $studentId,
                        ...$payload,
                    ]);
                }
            }

            if ($periodExamType !== null) {
                foreach (($data['period_exam'] ?? []) as $studentId => $value) {
                    $numericValue = $this->gradeCollectorService()->nullableNumber($value);
                    $payload = [
                        'valor' => $numericValue,
                        'activo' => $numericValue !== null,
                    ];

                    $periodExam = StudentPeriodExam::query()
                        ->where('asignacion_id', $data['asignacion_id'])
                        ->where('alumno_id', $studentId)
                        ->where('tipo', $periodExamType)
                        ->first();

                    if ($periodExam) {
                        $periodExam->update($payload);
                    } elseif ($payload['activo']) {
                        StudentPeriodExam::query()->create([
                            'asignacion_id' => $data['asignacion_id'],
                            'alumno_id' => $studentId,
                            'tipo' => $periodExamType,
                            ...$payload,
                        ]);
                    }
                }
            }
        });

        return redirect($this->gradebookRedirect($data))
            ->with('status', 'Colector guardado.');
    }

    public function import(Request $request, GradeCollectorImportService $importService): RedirectResponse
    {
        $data = $request->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignaciones,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'archivo' => ['required', 'file', 'max:10240'],
            'limpiar_antes_importar' => ['nullable', 'boolean'],
        ]);
        $this->guardAssignmentEditAccess((int) $data['asignacion_id']);

        try {
            $summary = $importService->import(
                $request->file('archivo'),
                (int) $data['asignacion_id'],
                (int) $data['trimestre_id'],
                $request->boolean('limpiar_antes_importar')
            );
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'archivo' => $exception->getMessage(),
            ]);
        }

        $message = sprintf(
            'Importacion lista. Categorias nuevas: %d, actualizadas: %d, notas nuevas: %d, actualizadas: %d, conducta nueva: %d, conducta actualizada: %d, alumnos vinculados: %d.',
            $summary['created_categories'],
            $summary['updated_categories'],
            $summary['created_grades'],
            $summary['updated_grades'],
            $summary['created_conduct'],
            $summary['updated_conduct'],
            $summary['matched_students']
        );

        if ($summary['errors'] !== []) {
            $message .= ' Observaciones: '.implode(' | ', array_slice($summary['errors'], 0, 5));
        }

        return redirect($this->gradebookRedirect($data))
            ->with('status', $message);
    }

    public function template(string $template): BinaryFileResponse
    {
        $templates = [
            'colector' => public_path('templates/plantilla_colector_notas.csv'),
            'normalizado' => public_path('templates/plantilla_notas_normalizada.csv'),
        ];

        abort_unless(isset($templates[$template]) && File::exists($templates[$template]), 404);

        return response()->download($templates[$template]);
    }

    public function applyTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignaciones,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'template_key' => ['required', 'string'],
            'reemplazar_existentes' => ['nullable', 'boolean'],
        ]);
        $this->guardAssignmentEditAccess((int) $data['asignacion_id']);

        $template = $this->collectorTemplateCatalog()->findByCode($data['template_key']);

        if (! $template || $template->items->isEmpty()) {
            throw ValidationException::withMessages([
                'template_key' => 'La plantilla seleccionada no es valida.',
            ]);
        }

        $hasActiveCategories = CollectorCategory::query()
            ->where('asignacion_id', $data['asignacion_id'])
            ->where('trimestre_id', $data['trimestre_id'])
            ->where('activo', true)
            ->exists();

        if ($hasActiveCategories && ! $request->boolean('reemplazar_existentes')) {
            throw ValidationException::withMessages([
                'template_key' => 'Ya existen categorias activas. Marca reemplazar si deseas usar la plantilla.',
            ]);
        }

        DB::transaction(function () use ($data, $template, $request): void {
            if ($request->boolean('reemplazar_existentes')) {
                $categoryIds = CollectorCategory::query()
                    ->where('asignacion_id', $data['asignacion_id'])
                    ->where('trimestre_id', $data['trimestre_id'])
                    ->where('activo', true)
                    ->pluck('id');

                if ($categoryIds->isNotEmpty()) {
                    CollectorCategory::query()
                        ->whereIn('id', $categoryIds)
                        ->update(['activo' => false]);

                    StudentCategoryGrade::query()
                        ->whereIn('categoria_id', $categoryIds)
                        ->update(['activo' => false]);
                }
            }

            foreach ($template->items as $index => $category) {
                CollectorCategory::query()->create([
                    'asignacion_id' => $data['asignacion_id'],
                    'trimestre_id' => $data['trimestre_id'],
                    'nombre' => $category->nombre,
                    'porcentaje' => $category->porcentaje,
                    'tipo_calculo' => $category->tipo_calculo,
                    'cantidad_notas' => $this->gradeCollectorService()->quantityForType($category->tipo_calculo),
                    'orden' => $category->orden ?? ($index + 1),
                    'activo' => true,
                ]);
            }
        });

        return redirect($this->gradebookRedirect($data))
            ->with('status', 'Plantilla aplicada correctamente.');
    }

    private function validateCategory(Request $request, ?CollectorCategory $category = null): array
    {
        return $request->validate([
            'asignacion_id' => ['required', 'integer', 'exists:asignaciones,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categorias_evaluacion', 'nombre')
                    ->where(fn ($query) => $query
                        ->where('asignacion_id', $request->input('asignacion_id'))
                        ->where('trimestre_id', $request->input('trimestre_id'))
                        ->where('activo', true))
                    ->ignore($category?->id),
            ],
            'porcentaje' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'tipo_calculo' => ['required', Rule::in(['normal', 'laboratorio'])],
            'orden' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
    }

    private function guardCategoryPercentage(int $assignmentId, int $trimesterId, float $newPercentage, ?int $ignoreId = null): void
    {
        $query = CollectorCategory::query()
            ->where('asignacion_id', $assignmentId)
            ->where('trimestre_id', $trimesterId)
            ->where('activo', true);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $total = (float) $query->sum('porcentaje');

        if (round($total + $newPercentage, 2) > 100) {
            throw ValidationException::withMessages([
                'porcentaje' => 'La suma de porcentajes no puede superar 100%.',
            ]);
        }
    }

    private function normalizeGradePayload(CollectorCategory $category, array $notes): array
    {
        $payload = [
            'nota_1' => $this->gradeCollectorService()->nullableNumber($notes['nota_1'] ?? null),
            'nota_2' => $this->gradeCollectorService()->nullableNumber($notes['nota_2'] ?? null),
            'nota_3' => $category->tipo_calculo === 'normal' ? $this->gradeCollectorService()->nullableNumber($notes['nota_3'] ?? null) : null,
            'nota_4' => $category->tipo_calculo === 'normal' ? $this->gradeCollectorService()->nullableNumber($notes['nota_4'] ?? null) : null,
        ];

        return [
            ...$payload,
            ...$this->gradeCollectorService()->calculateCategoryTotals($category, $payload),
        ];
    }

    private function gradebookRedirect(array $data): string
    {
        $url = '/pad/notas?anio_escolar='.$this->assignmentYear((int) $data['asignacion_id'])
            .'&seccion_id='.$this->assignmentSection((int) $data['asignacion_id'])
            .'&materia_id='.$this->assignmentSubject((int) $data['asignacion_id'])
            .'&trimestre_id='.(int) $data['trimestre_id'];

        if (! empty($data['periodo'])) {
            $url .= '&periodo='.urlencode((string) $data['periodo']);
        }

        return $url;
    }

    private function assignmentYear(int $assignmentId): int
    {
        return (int) DB::table('asignaciones')->where('id', $assignmentId)->value('anio_escolar');
    }

    private function assignmentSection(int $assignmentId): int
    {
        return (int) DB::table('asignaciones')->where('id', $assignmentId)->value('seccion_id');
    }

    private function assignmentSubject(int $assignmentId): int
    {
        return (int) DB::table('asignaciones')->where('id', $assignmentId)->value('materia_id');
    }

    private function gradeCollectorService(): GradeCollectorService
    {
        return app(GradeCollectorService::class);
    }

    private function periodExamType(int $trimesterId): ?string
    {
        $trimesterNumber = (int) DB::table('trimestres')
            ->where('id', $trimesterId)
            ->value('numero');

        if (in_array($trimesterNumber, [1, 2], true)) {
            return 'semestral';
        }

        if (in_array($trimesterNumber, [3, 4], true)) {
            return 'final';
        }

        return null;
    }

    private function resolvePeriodExamType(array $data): ?string
    {
        $selectedPeriod = (string) ($data['periodo'] ?? '');

        if ($selectedPeriod === 'exam:semestral') {
            return 'semestral';
        }

        if ($selectedPeriod === 'exam:final') {
            return 'final';
        }

        return $this->periodExamType((int) $data['trimestre_id']);
    }

    private function collectorTemplateCatalog(): CollectorTemplateCatalog
    {
        return app(CollectorTemplateCatalog::class);
    }

    private function guardAssignmentEditAccess(int $assignmentId): void
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->isProfessor()) {
            return;
        }

        $teacherId = (int) DB::table('profesores')
            ->where('usuario_id', $user->id)
            ->where('activo', true)
            ->value('id');

        $allowed = DB::table('asignaciones')
            ->where('id', $assignmentId)
            ->where('activo', true)
            ->where('profesor_id', $teacherId)
            ->exists();

        abort_unless($allowed, 403);
    }
}
