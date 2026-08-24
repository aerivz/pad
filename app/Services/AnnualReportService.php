<?php

namespace App\Services;

use App\Models\StudentPeriodExam;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnnualReportService
{
    public function buildStudentReport(int $studentId, ?int $sectionId = null): ?array
    {
        $student = DB::table('alumnos as a')
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'a.seccion_id')->where('s.activo', true);
            })
            ->where('a.activo', true)
            ->where('a.id', $studentId)
            ->when($sectionId, fn ($query) => $query->where('s.id', $sectionId))
            ->selectRaw("a.id, a.seccion_id, a.nombres, a.apellidos, CONCAT(a.nombres, ' ', a.apellidos) as full_name, CONCAT(s.grado, ' ', s.nombre) as section_name, s.anio_escolar")
            ->first();

        if (! $student) {
            return null;
        }

        $trimesters = DB::table('trimestres')
            ->orderBy('numero')
            ->get(['id', 'nombre', 'numero']);

        $assignments = DB::table('asignaciones as ag')
            ->where('ag.activo', true)
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->leftJoin('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->where('ag.seccion_id', $student->seccion_id)
            ->where('ag.anio_escolar', $student->anio_escolar)
            ->selectRaw("ag.id, ag.profesor_id, m.nombre as subject_name, TRIM(CONCAT(COALESCE(p.nombres, ''), ' ', COALESCE(p.apellidos, ''))) as teacher_name")
            ->orderBy('m.nombre')
            ->get();

        if ($assignments->isEmpty()) {
            return null;
        }

        $finalRows = DB::query()
            ->fromSub($this->subjectFinalsSubquery(), 'sf')
            ->where('sf.alumno_id', $studentId)
            ->whereIn('sf.asignacion_id', $assignments->pluck('id'))
            ->get()
            ->groupBy('asignacion_id')
            ->map(fn (Collection $rows) => $rows->keyBy('trimestre_id'));

        $conductRows = DB::table('conducta_alumnos')
            ->where('activo', true)
            ->where('alumno_id', $studentId)
            ->whereIn('asignacion_id', $assignments->pluck('id'))
            ->get(['asignacion_id', 'trimestre_id', 'valor'])
            ->groupBy('asignacion_id')
            ->map(fn (Collection $rows) => $rows->keyBy('trimestre_id'));

        $periodExamRows = StudentPeriodExam::query()
            ->where('activo', true)
            ->where('alumno_id', $studentId)
            ->whereIn('asignacion_id', $assignments->pluck('id'))
            ->get(['asignacion_id', 'tipo', 'valor'])
            ->groupBy('asignacion_id')
            ->map(fn (Collection $rows) => $rows->keyBy('tipo'));

        $subjects = $assignments->map(function ($assignment) use ($trimesters, $finalRows, $conductRows, $periodExamRows) {
            $trimesterData = [];
            $periodAverages = [];
            $conductAverages = [];

            foreach ($trimesters as $trimester) {
                $final = $finalRows->get($assignment->id)?->get($trimester->id);
                $conduct = $conductRows->get($assignment->id)?->get($trimester->id);
                $periodAverage = $final->nota_final ?? null;
                $conductValue = $conduct->valor ?? null;

                if ($periodAverage !== null) {
                    $periodAverages[] = (float) $periodAverage;
                }

                if ($conductValue !== null) {
                    $conductAverages[] = (float) $conductValue;
                }

                $trimesterData[$trimester->numero] = [
                    'progress_1' => $final->progress_1 ?? null,
                    'progress_2' => $final->progress_2 ?? null,
                    'period_average' => $periodAverage,
                    'conduct' => $conductValue,
                ];
            }

            $semesterEvaluation = $periodExamRows->get($assignment->id)?->get('semestral')?->valor;
            $finalEvaluation = $periodExamRows->get($assignment->id)?->get('final')?->valor;

            return [
                'subject' => $assignment->subject_name,
                'teacher' => $assignment->teacher_name ?: 'Sin asignar',
                'terms' => $trimesterData,
                'semester_evaluation' => $semesterEvaluation,
                'final_evaluation' => $finalEvaluation,
                'final_conduct' => $this->averageValues($conductAverages),
                'final_period' => $this->weightedAnnualGrade([
                    't1' => $trimesterData[1]['period_average'] ?? null,
                    't2' => $trimesterData[2]['period_average'] ?? null,
                    't3' => $trimesterData[3]['period_average'] ?? null,
                    't4' => $trimesterData[4]['period_average'] ?? null,
                    'semestral' => $semesterEvaluation,
                    'final' => $finalEvaluation,
                ]),
            ];
        })->values();

        $teacherName = collect($subjects)->pluck('teacher')->filter()->first() ?: 'Sin asignar';

        return [
            'student' => [
                'id' => $student->id,
                'section_id' => $student->seccion_id,
                'full_name' => $student->full_name,
                'section_name' => $student->section_name,
                'academic_year' => $student->anio_escolar,
            ],
            'teacher_name' => $teacherName,
            'terms' => $trimesters->keyBy('numero'),
            'subjects' => $subjects,
            'teacher_comment' => 'Hemos finalizado un ciclo academico con buen avance. Sigue esforzandote y manteniendo constancia en cada materia.',
            'signatures' => [
                'headmaster' => 'Dirección Academica',
                'teacher' => $teacherName,
            ],
        ];
    }

    public function buildSectionReports(int $sectionId): Collection
    {
        $studentIds = DB::table('alumnos')
            ->where('activo', true)
            ->where('seccion_id', $sectionId)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->pluck('id');

        return $studentIds
            ->map(fn ($studentId) => $this->buildStudentReport((int) $studentId, $sectionId))
            ->filter()
            ->values();
    }

    public function renderStudentPdfBytes(int $studentId, ?int $sectionId = null): ?string
    {
        $report = $this->buildStudentReport($studentId, $sectionId);

        if (! $report) {
            return null;
        }

        return Pdf::loadView('panel.reportcard-pdf', [
            'reports' => collect([$report]),
        ])->setPaper('legal', 'landscape')->output();
    }

    private function averageValues(iterable $values): ?float
    {
        $filtered = collect($values)
            ->filter(fn ($value) => $value !== null && $value !== '');

        if ($filtered->isEmpty()) {
            return null;
        }

        return round($filtered->avg(), 2);
    }

    private function weightedAnnualGrade(array $components): ?float
    {
        $required = ['t1', 't2', 't3', 't4', 'semestral', 'final'];

        foreach ($required as $key) {
            if (! array_key_exists($key, $components) || $components[$key] === null || $components[$key] === '') {
                return null;
            }
        }

        return round(
            ((float) $components['t1'] * 0.20)
            + ((float) $components['t2'] * 0.20)
            + ((float) $components['t3'] * 0.20)
            + ((float) $components['t4'] * 0.20)
            + ((float) $components['semestral'] * 0.10)
            + ((float) $components['final'] * 0.10),
            2
        );
    }

    private function subjectFinalsSubquery()
    {
        $totals = DB::table('categorias_evaluacion')
            ->where('activo', true)
            ->whereNotNull('asignacion_id')
            ->whereNotNull('trimestre_id')
            ->selectRaw('asignacion_id, trimestre_id, ROUND(SUM(porcentaje), 2) as porcentaje_total')
            ->groupBy('asignacion_id', 'trimestre_id');

        return DB::table('notas_alumnos as na')
            ->where('na.activo', true)
            ->join('categorias_evaluacion as c', function ($join) {
                $join->on('c.id', '=', 'na.categoria_id')->where('c.activo', true);
            })
            ->join('trimestres as t', 't.id', '=', 'c.trimestre_id')
            ->joinSub($totals, 'ct', function ($join) {
                $join->on('ct.asignacion_id', '=', 'c.asignacion_id')
                    ->on('ct.trimestre_id', '=', 'c.trimestre_id');
            })
            ->selectRaw('na.alumno_id, c.asignacion_id, c.trimestre_id, t.numero as trimestre_numero, ROUND(SUM(COALESCE(na.promedio_1, 0)), 2) as progress_1, ROUND(SUM(COALESCE(na.promedio_2, 0)), 2) as progress_2, CASE WHEN ct.porcentaje_total = 100 THEN ROUND((SUM(COALESCE(na.promedio_1, 0)) + SUM(COALESCE(na.promedio_2, 0))) / 2, 2) ELSE NULL END as nota_final')
            ->groupBy('na.alumno_id', 'c.asignacion_id', 'c.trimestre_id', 't.numero', 'ct.porcentaje_total');
    }
}
