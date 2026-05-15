<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentConduct extends Model
{
    protected $table = 'conducta_alumnos';

    public $timestamps = false;

    protected $fillable = [
        'asignacion_id',
        'trimestre_id',
        'alumno_id',
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
