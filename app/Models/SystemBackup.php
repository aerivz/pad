<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemBackup extends Model
{
    protected $table = 'backups_sistema';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'archivo_zip',
        'estado',
        'tamano_bytes',
        'total_archivos',
        'metadatos',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'metadatos' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function isReady(): bool
    {
        return $this->estado === 'completado' && filled($this->archivo_zip);
    }
}
