<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmailTemplate extends Model
{
    protected $table = 'plantillas_correo';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'asunto',
        'cuerpo_html',
        'documentos_generados',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'documentos_generados' => 'array',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'plantilla_correo_rol', 'plantilla_id', 'rol_id');
    }
}
