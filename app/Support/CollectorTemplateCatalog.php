<?php

namespace App\Support;

use App\Models\CollectorTemplate;
use Illuminate\Support\Collection;

class CollectorTemplateCatalog
{
    public function collection(): Collection
    {
        return CollectorTemplate::active()
            ->with('items')
            ->orderBy('nombre')
            ->get();
    }

    public function keyed(): Collection
    {
        return $this->collection()
            ->keyBy('codigo')
            ->map(function (CollectorTemplate $template): array {
                return [
                    'id' => $template->id,
                    'code' => $template->codigo,
                    'name' => $template->nombre,
                    'description' => $template->descripcion,
                    'categories' => $template->items->map(fn ($item) => [
                        'id' => $item->id,
                        'nombre' => $item->nombre,
                        'porcentaje' => (float) $item->porcentaje,
                        'tipo_calculo' => $item->tipo_calculo,
                        'cantidad_notas' => $item->cantidad_notas,
                        'orden' => $item->orden,
                    ])->values()->all(),
                ];
            });
    }

    public function codes(): array
    {
        return $this->collection()->pluck('codigo')->all();
    }

    public function firstCode(): ?string
    {
        return $this->collection()->sortBy('id')->first()?->codigo;
    }

    public function findByCode(?string $code): ?CollectorTemplate
    {
        if (! $code) {
            return null;
        }

        return CollectorTemplate::active()
            ->with('items')
            ->where('codigo', $code)
            ->first();
    }
}
