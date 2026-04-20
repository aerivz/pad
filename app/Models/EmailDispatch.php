<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailDispatch extends Model
{
    protected $table = 'envios_correo';

    public $timestamps = false;

    protected $fillable = [
        'plantilla_id',
        'padre_id',
        'alumno_id',
        'trimestre_id',
        'estado',
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
