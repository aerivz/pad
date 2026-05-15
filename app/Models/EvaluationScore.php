<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EvaluationScore extends Model
{
    protected $table = 'evaluacion_notas';

    public $timestamps = false;

    protected $fillable = [
        'evaluacion_id',
        'alumno_id',
        'valor',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'valor' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
