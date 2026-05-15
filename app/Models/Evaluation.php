<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $table = 'evaluaciones';

    public $timestamps = false;

    protected $fillable = [
        'asignacion_id',
        'trimestre_id',
        'categoria_id',
        'nombre',
        'ponderacion',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'ponderacion' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
