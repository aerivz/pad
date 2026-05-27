<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $table = 'asignaciones';

    public $timestamps = false;

    protected $fillable = [
        'seccion_id',
        'materia_id',
        'profesor_id',
        'anio_escolar',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
