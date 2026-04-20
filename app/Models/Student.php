<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'alumnos';

    public $timestamps = false;

    protected $fillable = [
        'seccion_id',
        'nombres',
        'apellidos',
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

    public function familyMembers(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'padre_alumno', 'alumno_id', 'padre_id')
            ->withPivot('id', 'parentesco');
    }
}
