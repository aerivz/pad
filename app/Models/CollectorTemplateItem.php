<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectorTemplateItem extends Model
{
    protected $table = 'plantillas_colector_detalle';

    protected $fillable = [
        'plantilla_id',
        'nombre',
        'porcentaje',
        'tipo_calculo',
        'cantidad_notas',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'float',
            'activo' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CollectorTemplate::class, 'plantilla_id');
    }
}
