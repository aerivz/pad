<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = [
            'alumnos' => DB::table('alumnos')->where('activo', true)->count(),
            'secciones' => DB::table('secciones')->where('activo', true)->count(),
            'profesores' => DB::table('profesores')->where('activo', true)->count(),
            'notas' => DB::table('notas')->count(),
        ];

        $sections = DB::table('secciones as s')
            ->where('s.activo', true)
            ->leftJoin('alumnos as a', function ($join) {
                $join->on('a.seccion_id', '=', 's.id')->where('a.activo', true);
            })
            ->leftJoin('asignaciones as ag', 'ag.seccion_id', '=', 's.id')
            ->leftJoin('notas as n', 'n.asignacion_id', '=', 'ag.id')
            ->groupBy('s.id', 's.nombre', 's.grado', 's.anio_escolar')
            ->selectRaw('s.id, s.nombre, s.grado, s.anio_escolar, COUNT(DISTINCT a.id) as total_alumnos, COUNT(DISTINCT ag.materia_id) as total_materias, ROUND(AVG(n.valor), 1) as promedio')
            ->orderBy('s.id')
            ->get();

        $students = DB::table('alumnos as a')
            ->where('a.activo', true)
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'a.seccion_id')->where('s.activo', true);
            })
            ->leftJoin('padre_alumno as pa', 'pa.alumno_id', '=', 'a.id')
            ->leftJoin('notas as n', 'n.alumno_id', '=', 'a.id')
            ->groupBy('a.id', 'a.nombres', 'a.apellidos', 's.grado', 's.nombre', 'a.seccion_id')
            ->selectRaw('a.id, a.seccion_id, a.nombres, a.apellidos, s.grado, s.nombre as seccion_nombre, COUNT(DISTINCT pa.padre_id) as total_padres, ROUND(AVG(n.valor), 1) as promedio')
            ->orderBy('a.id')
            ->get();

        $teachers = DB::table('profesores as p')
            ->where('p.activo', true)
            ->leftJoin('asignaciones as ag', 'ag.profesor_id', '=', 'p.id')
            ->leftJoin('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->leftJoin('secciones as s', function ($join) {
                $join->on('s.id', '=', 'ag.seccion_id')->where('s.activo', true);
            })
            ->groupBy('p.id', 'p.nombres', 'p.apellidos', 'p.especialidad', 'p.email')
            ->selectRaw("p.id, p.nombres, p.apellidos, p.especialidad, p.email, COUNT(DISTINCT ag.id) as total_asignaciones, COUNT(DISTINCT s.id) as total_secciones, GROUP_CONCAT(DISTINCT m.nombre ORDER BY m.nombre SEPARATOR ' | ') as materias")
            ->orderBy('p.id')
            ->get();

        $subjects = DB::table('materias as m')
            ->where('m.activo', true)
            ->leftJoin('asignaciones as ag', 'ag.materia_id', '=', 'm.id')
            ->leftJoin('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->leftJoin('notas as n', 'n.asignacion_id', '=', 'ag.id')
            ->groupBy('m.id', 'm.nombre')
            ->selectRaw("m.id, m.nombre, COUNT(DISTINCT p.id) as total_profesores, COUNT(DISTINCT ag.seccion_id) as total_secciones, ROUND(AVG(n.valor), 1) as promedio")
            ->orderBy('m.id')
            ->get();

        $parents = DB::table('padres as p')
            ->where('p.activo', true)
            ->leftJoin('padre_alumno as pa', 'pa.padre_id', '=', 'p.id')
            ->leftJoin('envios_correo as ec', 'ec.padre_id', '=', 'p.id')
            ->groupBy('p.id', 'p.nombres', 'p.apellidos', 'p.email_principal')
            ->selectRaw("p.id, p.nombres, p.apellidos, p.email_principal, COUNT(DISTINCT pa.alumno_id) as total_hijos, MAX(ec.id) as ultimo_envio_id")
            ->orderBy('p.id')
            ->get();

        $users = DB::table('usuarios as u')
            ->where('u.activo', true)
            ->join('roles as r', 'r.id', '=', 'u.rol_id')
            ->select('u.id', 'u.rol_id', 'u.nombre_usuario', 'u.email', 'u.nombres', 'u.apellidos', 'u.activo', 'r.nombre as rol')
            ->orderBy('u.id')
            ->get();

        $emails = DB::table('envios_correo as ec')
            ->join('plantillas_correo as pc', 'pc.id', '=', 'ec.plantilla_id')
            ->join('padres as p', function ($join) {
                $join->on('p.id', '=', 'ec.padre_id')->where('p.activo', true);
            })
            ->join('alumnos as a', function ($join) {
                $join->on('a.id', '=', 'ec.alumno_id')->where('a.activo', true);
            })
            ->join('trimestres as t', 't.id', '=', 'ec.trimestre_id')
            ->select('ec.id', 'ec.estado', 'pc.nombre as plantilla', 'p.nombres', 'p.apellidos', 'p.email_principal', 'a.nombres as alumno_nombres', 'a.apellidos as alumno_apellidos', 't.nombre as trimestre')
            ->orderByDesc('ec.id')
            ->get();

        $audit = DB::table('auditoria_notas as an')
            ->join('usuarios as u', 'u.id', '=', 'an.usuario_id')
            ->join('notas as n', 'n.id', '=', 'an.nota_id')
            ->select('an.*', 'u.nombre_usuario', 'n.alumno_id')
            ->orderByDesc('an.id')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'stats' => $stats,
            'sections' => $sections,
            'students' => $students,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'parents' => $parents,
            'users' => $users,
            'emails' => $emails,
            'audit' => $audit,
            'gradeBoard' => $this->buildGradeBoard(),
            'reportCard' => $this->buildReportCard(),
            'roles' => DB::table('roles')->where('activo', true)->orderBy('nombre')->get(),
            'activeTab' => $request->query('tab', 's-dashboard'),
            'editSection' => $request->filled('edit_section') ? Section::active()->find($request->integer('edit_section')) : null,
            'editStudent' => $request->filled('edit_student') ? Student::active()->find($request->integer('edit_student')) : null,
            'editTeacher' => $request->filled('edit_teacher') ? Teacher::active()->find($request->integer('edit_teacher')) : null,
            'editSubject' => $request->filled('edit_subject') ? Subject::active()->find($request->integer('edit_subject')) : null,
            'editGuardian' => $request->filled('edit_guardian') ? Guardian::active()->find($request->integer('edit_guardian')) : null,
            'editUser' => $request->filled('edit_user') ? User::active()->find($request->integer('edit_user')) : null,
        ]);
    }

    private function buildGradeBoard(): array
    {
        $assignment = DB::table('asignaciones as ag')
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'ag.seccion_id')->where('s.activo', true);
            })
            ->join('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->select('ag.id', 'ag.seccion_id', 'm.nombre as materia', 's.grado', 's.nombre as seccion', DB::raw("CONCAT(p.nombres, ' ', p.apellidos) as profesor"))
            ->orderBy('ag.id')
            ->first();

        $categories = DB::table('categorias_evaluacion')->orderBy('id')->get();

        if (! $assignment) {
            return ['assignment' => null, 'categories' => collect(), 'rows' => collect()];
        }

        $rows = DB::table('alumnos as a')
            ->where('a.activo', true)
            ->where('a.seccion_id', $assignment->seccion_id)
            ->leftJoin('notas as n', function ($join) use ($assignment) {
                $join->on('n.alumno_id', '=', 'a.id')
                    ->where('n.asignacion_id', '=', $assignment->id)
                    ->where('n.trimestre_id', '=', 1);
            })
            ->select('a.id', 'a.nombres', 'a.apellidos', 'n.categoria_id', 'n.valor')
            ->orderBy('a.id')
            ->get()
            ->groupBy('id')
            ->map(function (Collection $records) use ($categories) {
                $first = $records->first();
                $grades = $records->pluck('valor', 'categoria_id');
                $final = $categories->sum(fn ($category) => (float) ($grades[$category->id] ?? 0) * ((float) $category->porcentaje / 100));

                return [
                    'id' => $first->id,
                    'nombre' => trim($first->nombres.' '.$first->apellidos),
                    'grades' => $categories->mapWithKeys(fn ($category) => [$category->id => $grades[$category->id] ?? null]),
                    'final' => round($final, 1),
                ];
            })->values();

        return ['assignment' => $assignment, 'categories' => $categories, 'rows' => $rows];
    }

    private function buildReportCard(): Collection
    {
        return DB::table('notas as n')
            ->join('alumnos as a', function ($join) {
                $join->on('a.id', '=', 'n.alumno_id')->where('a.activo', true);
            })
            ->join('asignaciones as ag', 'ag.id', '=', 'n.asignacion_id')
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->selectRaw("a.id, CONCAT(a.nombres, ' ', a.apellidos) as alumno, m.nombre as materia, ROUND(AVG(n.valor), 1) as promedio")
            ->groupBy('a.id', 'a.nombres', 'a.apellidos', 'm.nombre')
            ->orderBy('a.id')
            ->get()
            ->groupBy('id')
            ->map(function (Collection $studentRows) {
                $first = $studentRows->first();

                return [
                    'alumno' => $first->alumno,
                    'materias' => $studentRows->pluck('promedio', 'materia'),
                    'promedio' => round($studentRows->avg('promedio'), 1),
                ];
            })->values();
    }
}
