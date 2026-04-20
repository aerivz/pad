<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $table = 'padres';

    public $timestamps = false;

    protected $fillable = [
        'nombres',
        'apellidos',
        'email_principal',
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

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'padre_alumno', 'padre_id', 'alumno_id')
            ->withPivot('id', 'parentesco');
    }
}
