<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'roles';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
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

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'rol_menu', 'rol_id', 'menu_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    public function emailTemplates(): BelongsToMany
    {
        return $this->belongsToMany(EmailTemplate::class, 'plantilla_correo_rol', 'rol_id', 'plantilla_id');
    }
}
