<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentAccess
{
    /**
     * Null means unrestricted access. A collection contains the allowed IDs.
     */
    public function studentIds(User $user): ?Collection
    {
        $role = $user->role()->value('nombre');

        if (in_array($role, ['admin', 'director', 'secretaria'], true)) {
            return null;
        }

        if ($role === 'profesor') {
            $teacherId = $user->teacher()->where('activo', true)->value('id');

            if (! $teacherId) {
                return collect();
            }

            return DB::table('alumnos as a')
                ->where('a.activo', true)
                ->where(function ($query) use ($teacherId): void {
                    $query->whereIn('a.seccion_id', function ($sections) use ($teacherId): void {
                        $sections->from('secciones')
                            ->select('id')
                            ->where('activo', true)
                            ->where('titular_profesor_id', $teacherId);
                    })->orWhereIn('a.seccion_id', function ($sections) use ($teacherId): void {
                        $sections->from('asignaciones')
                            ->select('seccion_id')
                            ->where('activo', true)
                            ->where('profesor_id', $teacherId);
                    });
                })
                ->pluck('a.id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }

        if ($role === 'padre') {
            return DB::table('padres as p')
                ->join('padre_alumno as pa', 'pa.padre_id', '=', 'p.id')
                ->join('alumnos as a', function ($join): void {
                    $join->on('a.id', '=', 'pa.alumno_id')->where('a.activo', true);
                })
                ->where('p.activo', true)
                ->where('p.usuario_id', $user->id)
                ->pluck('a.id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
        }

        return collect();
    }

    public function sectionIds(User $user): ?Collection
    {
        $studentIds = $this->studentIds($user);

        if ($studentIds === null) {
            return null;
        }

        return DB::table('alumnos')
            ->whereIn('id', $studentIds)
            ->pluck('seccion_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    public function allowsStudent(User $user, int $studentId): bool
    {
        $studentIds = $this->studentIds($user);

        return $studentIds === null || $studentIds->contains($studentId);
    }

    public function allowsSection(User $user, int $sectionId): bool
    {
        $sectionIds = $this->sectionIds($user);

        return $sectionIds === null || $sectionIds->contains($sectionId);
    }
}
