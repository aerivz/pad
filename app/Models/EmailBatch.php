<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailBatch extends Model
{
    protected $table = 'lotes_envio_correo';

    protected $fillable = [
        'usuario_id',
        'plantilla_id',
        'seccion_id',
        'trimestre_id',
        'nombre',
        'estado',
        'total',
        'procesados',
        'enviados',
        'fallidos',
        'omitidos',
        'iniciado_en',
        'finalizado_en',
        'notificado_en',
    ];

    protected function casts(): array
    {
        return [
            'iniciado_en' => 'datetime',
            'finalizado_en' => 'datetime',
            'notificado_en' => 'datetime',
        ];
    }

    public function template(): BelongsTo { return $this->belongsTo(EmailTemplate::class, 'plantilla_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class, 'seccion_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'usuario_id'); }
    public function dispatches(): HasMany { return $this->hasMany(EmailDispatch::class, 'lote_id'); }
}
