<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectorCategory extends Model
{
    protected $table = 'categorias_evaluacion';

    public $timestamps = false;

    protected $fillable = [
        'asignacion_id',
        'trimestre_id',
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
            'porcentaje' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(StudentCategoryGrade::class, 'categoria_id');
    }
}
