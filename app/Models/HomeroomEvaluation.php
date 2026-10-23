<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeroomEvaluation extends Model
{
    protected $table = 'evaluaciones_titular';

    public $timestamps = false;

    protected $fillable = [
        'seccion_id',
        'trimestre_id',
        'alumno_id',
        'conducta',
        'comentario',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'conducta' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }
}
