<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDispatch extends Model
{
    protected $table = 'envios_correo';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'plantilla_id',
        'padre_id',
        'alumno_id',
        'trimestre_id',
        'estado',
        'destinatario_email',
        'adjuntos_generados',
        'error_mensaje',
        'enviado_en',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'adjuntos_generados' => 'array',
            'enviado_en' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'plantilla_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
