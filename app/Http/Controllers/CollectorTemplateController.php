<?php

namespace App\Http\Controllers;

use App\Models\CollectorTemplate;
use App\Models\CollectorTemplateItem;
use App\Models\Subject;
use App\Services\GradeCollectorService;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CollectorTemplateController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTemplate($request);

        DB::transaction(function () use ($data): void {
            $template = CollectorTemplate::query()->create([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'activo' => true,
            ]);

            $this->syncItems($template, $data['categorias']);
        });

        return redirect(AppUrl::route('collector-templates.index'))
            ->with('status', 'Plantilla de colector creada correctamente.');
    }

    public function update(Request $request, CollectorTemplate $template): RedirectResponse
    {
        $data = $this->validateTemplate($request, $template);
        $previousCode = $template->codigo;

        DB::transaction(function () use ($template, $data, $previousCode): void {
            $template->update([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
            ]);

            if ($previousCode !== $data['codigo']) {
                Subject::query()
                    ->where('plantilla_colector', $previousCode)
                    ->update(['plantilla_colector' => $data['codigo']]);
            }

            CollectorTemplateItem::query()
                ->where('plantilla_id', $template->id)
                ->where('activo', true)
                ->update(['activo' => false]);

            $this->syncItems($template, $data['categorias']);
        });

        return redirect(AppUrl::route('collector-templates.index'))
            ->with('status', 'Plantilla de colector actualizada correctamente.');
    }

    public function destroy(CollectorTemplate $template): RedirectResponse
    {
        $linkedSubjects = Subject::active()
            ->where('plantilla_colector', $template->codigo)
            ->count();

        if ($linkedSubjects > 0) {
            throw ValidationException::withMessages([
                'template' => "No se puede desactivar. Hay {$linkedSubjects} materia(s) usando esta plantilla.",
            ]);
        }

        DB::transaction(function () use ($template): void {
            $template->update(['activo' => false]);

            CollectorTemplateItem::query()
                ->where('plantilla_id', $template->id)
                ->update(['activo' => false]);
        });

        return redirect(AppUrl::route('collector-templates.index'))
            ->with('status', 'Plantilla de colector desactivada correctamente.');
    }

    private function validateTemplate(Request $request, ?CollectorTemplate $template = null): array
    {
        $data = $request->validate([
            'codigo' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z0-9_\\-]+$/',
                Rule::unique('plantillas_colector', 'codigo')->ignore($template?->id),
            ],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'categorias' => ['required', 'array', 'min:1'],
            'categorias.*.nombre' => ['required', 'string', 'max:120'],
            'categorias.*.porcentaje' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'categorias.*.tipo_calculo' => ['required', Rule::in(['normal', 'laboratorio'])],
            'categorias.*.orden' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $data['codigo'] = $data['codigo'] ?: Str::of($data['nombre'])->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->value();

        if ($data['codigo'] === '') {
            throw ValidationException::withMessages([
                'codigo' => 'No se pudo generar codigo valido para la plantilla.',
            ]);
        }

        $total = round(collect($data['categorias'])->sum(fn ($category) => (float) $category['porcentaje']), 2);

        if ($total !== 100.0) {
            throw ValidationException::withMessages([
                'categorias' => "La suma de porcentajes debe ser exactamente 100%. Total actual: {$total}%.",
            ]);
        }

        return $data;
    }

    private function syncItems(CollectorTemplate $template, array $categories): void
    {
        foreach ($categories as $index => $category) {
            CollectorTemplateItem::query()->create([
                'plantilla_id' => $template->id,
                'nombre' => $category['nombre'],
                'porcentaje' => $category['porcentaje'],
                'tipo_calculo' => $category['tipo_calculo'],
                'cantidad_notas' => $this->gradeCollectorService()->quantityForType($category['tipo_calculo']),
                'orden' => $category['orden'] ?? ($index + 1),
                'activo' => true,
            ]);
        }
    }

    private function gradeCollectorService(): GradeCollectorService
    {
        return app(GradeCollectorService::class);
    }
}
