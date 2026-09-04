<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    protected $table = 'asistencias_alumnos';

    protected $fillable = [
        'alumno_id',
        'seccion_id',
        'fecha',
        'estado',
        'justificante',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class, 'alumno_id'); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class, 'seccion_id'); }
    public function registeredBy(): BelongsTo { return $this->belongsTo(User::class, 'registrado_por'); }
}
