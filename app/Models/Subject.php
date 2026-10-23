<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'materias';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'plantilla_colector',
        'activo',
        'es_electiva',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'es_electiva' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
