<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'clave',
        'nombre',
        'descripcion',
        'icono',
        'url',
        'tablas_relacionadas',
        'orden',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('activo', true)->orderBy('orden');
    }

    public function getResolvedUrlAttribute(): string
    {
        return app_nav_url($this->url);
    }
}
