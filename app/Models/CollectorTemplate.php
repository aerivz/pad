<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectorTemplate extends Model
{
    protected $table = 'plantillas_colector';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
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

    public function items(): HasMany
    {
        return $this->hasMany(CollectorTemplateItem::class, 'plantilla_id')
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id');
    }
}
