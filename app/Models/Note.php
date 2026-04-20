<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notas';

    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'asignacion_id',
        'trimestre_id',
        'categoria_id',
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
