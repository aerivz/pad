<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentCategoryGrade extends Model
{
    protected $table = 'notas_alumnos';

    public $timestamps = false;

    protected $fillable = [
        'categoria_id',
        'alumno_id',
        'nota_1',
        'nota_2',
        'nota_3',
        'nota_4',
        'promedio_1',
        'promedio_2',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'nota_1' => 'decimal:2',
            'nota_2' => 'decimal:2',
            'nota_3' => 'decimal:2',
            'nota_4' => 'decimal:2',
            'promedio_1' => 'decimal:2',
            'promedio_2' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
