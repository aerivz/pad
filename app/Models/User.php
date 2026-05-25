<?php

namespace App\Models;

use App\Models\Concerns\ResolvesMediaUrls;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, ResolvesMediaUrls;

    protected $table = 'usuarios';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'rol_id',
        'nombre_usuario',
        'email',
        'password_hash',
        'nombres',
        'apellidos',
        'activo',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function allowedMenuKeys(): array
    {
        $role = $this->relationLoaded('role') ? $this->role : $this->role()->with('menus')->first();

        if (! $role) {
            return [];
        }

        $menus = $role->relationLoaded('menus') ? $role->menus : $role->menus()->get();

        return $menus
            ->where('activo', true)
            ->pluck('clave')
            ->all();
    }

    public function hasMenuAccess(string $menuKey): bool
    {
        return in_array($menuKey, $this->allowedMenuKeys(), true);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->resolveMediaUrl(
            data_get($this, 'avatar')
            ?: data_get($this, 'imagen')
            ?: data_get($this, 'foto'),
            'images/defaults/avatar.svg'
        );
    }
}
