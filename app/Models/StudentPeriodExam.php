<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentPeriodExam extends Model
{
    protected $table = 'evaluaciones_periodo_alumnos';

    public $timestamps = false;

    protected $fillable = [
        'asignacion_id',
        'alumno_id',
        'tipo',
        'valor',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
