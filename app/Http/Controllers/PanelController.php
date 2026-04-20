<?php

namespace App\Http\Controllers;

use App\Models\EmailDispatch;
use App\Models\Guardian;
use App\Models\Menu;
use App\Models\Note;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function dashboard(): View
    {
        return view('panel.dashboard', [
            ...$this->baseData(),
            'activeMenu' => 'dashboard',
            'audit' => $this->audit(),
        ]);
    }

    public function sections(): View
    {
        return view('panel.sections', [
            ...$this->baseData(),
            'activeMenu' => 'sections',
            'sections' => $this->sectionsData(),
            'editSection' => request()->filled('edit_section') ? Section::active()->find(request()->integer('edit_section')) : null,
        ]);
    }

    public function students(): View
    {
        return view('panel.students', [
            ...$this->baseData(),
            'activeMenu' => 'students',
            'sections' => $this->sectionsData(),
            'students' => $this->studentsData(),
            'editStudent' => request()->filled('edit_student') ? Student::active()->find(request()->integer('edit_student')) : null,
        ]);
    }

    public function teachers(): View
    {
        return view('panel.teachers', [
            ...$this->baseData(),
            'activeMenu' => 'teachers',
            'teachers' => $this->teachersData(),
            'editTeacher' => request()->filled('edit_teacher') ? Teacher::active()->find(request()->integer('edit_teacher')) : null,
        ]);
    }

    public function subjects(): View
    {
        return view('panel.subjects', [
            ...$this->baseData(),
            'activeMenu' => 'subjects',
            'subjects' => $this->subjectsData(),
            'editSubject' => request()->filled('edit_subject') ? Subject::active()->find(request()->integer('edit_subject')) : null,
        ]);
    }

    public function guardians(): View
    {
        return view('panel.guardians', [
            ...$this->baseData(),
            'activeMenu' => 'guardians',
            'parents' => $this->guardiansData(),
            'studentsForFamily' => $this->studentsForFamily(),
            'relationshipOptions' => $this->relationshipOptions(),
            'editGuardian' => request()->filled('edit_guardian') ? $this->guardianForEdit(request()->integer('edit_guardian')) : null,
        ]);
    }

    public function gradeBook(): View
    {
        $selectedAssignmentId = request()->integer('assignment_id');
        $selectedTrimesterId = request()->integer('trimestre_id', 1);

        return view('panel.gradebook', [
            ...$this->baseData(),
            'activeMenu' => 'gradebook',
            'gradeBoard' => $this->buildGradeBoard($selectedAssignmentId, $selectedTrimesterId),
            'noteRows' => $this->notesData(),
            'studentsForNotes' => $this->studentsForFamily(),
            'assignmentOptions' => $this->assignmentOptions(),
            'trimesters' => DB::table('trimestres')->orderBy('numero')->get(),
            'categories' => DB::table('categorias_evaluacion')->orderBy('id')->get(),
            'editNote' => request()->filled('edit_note') ? Note::active()->find(request()->integer('edit_note')) : null,
        ]);
    }

    public function reportCard(): View
    {
        $filters = [
            'seccion_id' => request()->integer('seccion_id'),
            'alumno_id' => request()->integer('alumno_id'),
            'trimestre_id' => request()->integer('trimestre_id'),
        ];

        return view('panel.reportcard', [
            ...$this->baseData(),
            'activeMenu' => 'reportcard',
            'subjectColumns' => $this->subjectColumns(),
            'reportCard' => $this->buildReportCard($filters),
            'reportFilters' => $filters,
            'reportStudents' => $this->studentsForFamily(),
            'reportSections' => Section::active()->orderBy('grado')->orderBy('nombre')->get(),
            'reportTrimesters' => DB::table('trimestres')->orderBy('numero')->get(),
        ]);
    }

    public function emails(): View
    {
        return view('panel.emails', [
            ...$this->baseData(),
            'activeMenu' => 'emails',
            'emails' => $this->emailsData(),
            'templates' => DB::table('plantillas_correo')->orderBy('nombre')->get(),
            'familyMembers' => Guardian::active()->orderBy('nombres')->orderBy('apellidos')->get(),
            'studentsForEmail' => $this->studentsForFamily(),
            'trimesters' => DB::table('trimestres')->orderBy('numero')->get(),
            'editEmail' => request()->filled('edit_email') ? EmailDispatch::active()->find(request()->integer('edit_email')) : null,
        ]);
    }

    public function users(): View
    {
        return view('panel.users', [
            ...$this->baseData(),
            'activeMenu' => 'users',
            'users' => $this->usersData(),
            'editUser' => request()->filled('edit_user') ? User::active()->find(request()->integer('edit_user')) : null,
        ]);
    }

    public function profiles(): View
    {
        return view('panel.profiles', [
            ...$this->baseData(),
            'activeMenu' => 'profiles',
            'profiles' => $this->profilesData(),
            'permissionMenus' => Menu::active()->orderBy('orden')->get(),
            'editProfile' => request()->filled('edit_profile') ? $this->profileForEdit(request()->integer('edit_profile')) : null,
        ]);
    }

    public function config(): View
    {
        return view('panel.config', [
            ...$this->baseData(),
            'activeMenu' => 'config',
        ]);
    }

    private function baseData(): array
    {
        return [
            'stats' => [
                'alumnos' => DB::table('alumnos')->where('activo', true)->count(),
                'secciones' => DB::table('secciones')->where('activo', true)->count(),
                'profesores' => DB::table('profesores')->where('activo', true)->count(),
                'notas' => DB::table('notas')->where('activo', true)->count(),
            ],
            'roles' => DB::table('roles')->where('activo', true)->orderBy('nombre')->get(),
            'menu' => $this->applicationMenu(),
        ];
    }

    private function applicationMenu(): array
    {
        $items = Menu::query()->where('activo', true)->orderBy('orden')->get();
        $user = Auth::user();

        if ($user) {
            $allowedMenuKeys = $user->allowedMenuKeys();
            $items = $items->whereIn('clave', $allowedMenuKeys)->values();
        }

        if ($items->isEmpty()) {
            return [];
        }

        return $items->mapWithKeys(fn (Menu $item) => [
            $item->clave => [
                'label' => $item->nombre,
                'icon' => $item->icono,
                'url' => $item->url,
            ],
        ])->all();
    }

    private function sectionsData()
    {
        return DB::table('secciones as s')
            ->where('s.activo', true)
            ->leftJoin('alumnos as a', function ($join) {
                $join->on('a.seccion_id', '=', 's.id')->where('a.activo', true);
            })
            ->leftJoin('asignaciones as ag', 'ag.seccion_id', '=', 's.id')
            ->leftJoin('notas as n', function ($join) {
                $join->on('n.asignacion_id', '=', 'ag.id')->where('n.activo', true);
            })
            ->groupBy('s.id', 's.nombre', 's.grado', 's.anio_escolar')
            ->selectRaw('s.id, s.nombre, s.grado, s.anio_escolar, COUNT(DISTINCT a.id) as total_alumnos, COUNT(DISTINCT ag.materia_id) as total_materias, ROUND(AVG(n.valor), 1) as promedio')
            ->orderBy('s.id')
            ->get();
    }

    private function studentsData()
    {
        return DB::table('alumnos as a')
            ->where('a.activo', true)
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'a.seccion_id')->where('s.activo', true);
            })
            ->leftJoin('padre_alumno as pa', 'pa.alumno_id', '=', 'a.id')
            ->leftJoin('notas as n', function ($join) {
                $join->on('n.alumno_id', '=', 'a.id')->where('n.activo', true);
            })
            ->groupBy('a.id', 'a.seccion_id', 'a.nombres', 'a.apellidos', 's.grado', 's.nombre')
            ->selectRaw('a.id, a.seccion_id, a.nombres, a.apellidos, s.grado, s.nombre as seccion_nombre, COUNT(DISTINCT pa.padre_id) as total_padres, ROUND(AVG(n.valor), 1) as promedio')
            ->orderBy('a.id')
            ->get();
    }

    private function teachersData()
    {
        return DB::table('profesores as p')
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
    }

    private function subjectsData()
    {
        return DB::table('materias as m')
            ->where('m.activo', true)
            ->leftJoin('asignaciones as ag', 'ag.materia_id', '=', 'm.id')
            ->leftJoin('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->leftJoin('notas as n', function ($join) {
                $join->on('n.asignacion_id', '=', 'ag.id')->where('n.activo', true);
            })
            ->groupBy('m.id', 'm.nombre')
            ->selectRaw('m.id, m.nombre, COUNT(DISTINCT p.id) as total_profesores, COUNT(DISTINCT ag.seccion_id) as total_secciones, ROUND(AVG(n.valor), 1) as promedio')
            ->orderBy('m.id')
            ->get();
    }

    private function guardiansData()
    {
        return DB::table('padres as p')
            ->where('p.activo', true)
            ->leftJoin('padre_alumno as pa', 'pa.padre_id', '=', 'p.id')
            ->leftJoin('alumnos as a', function ($join) {
                $join->on('a.id', '=', 'pa.alumno_id')->where('a.activo', true);
            })
            ->leftJoin('envios_correo as ec', function ($join) {
                $join->on('ec.padre_id', '=', 'p.id')->where('ec.activo', true);
            })
            ->groupBy('p.id', 'p.nombres', 'p.apellidos', 'p.email_principal')
            ->selectRaw("p.id, p.nombres, p.apellidos, p.email_principal, COUNT(DISTINCT pa.alumno_id) as total_hijos, MAX(ec.id) as ultimo_envio_id, GROUP_CONCAT(DISTINCT CONCAT(COALESCE(a.nombres, ''), ' ', COALESCE(a.apellidos, ''), ' (', pa.parentesco, ')') ORDER BY a.nombres SEPARATOR ' | ') as miembros")
            ->orderBy('p.id')
            ->get();
    }

    private function studentsForFamily()
    {
        return DB::table('alumnos as a')
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'a.seccion_id')->where('s.activo', true);
            })
            ->where('a.activo', true)
            ->selectRaw("a.id, CONCAT(a.nombres, ' ', a.apellidos, ' - ', s.grado, ' ', s.nombre) as nombre_completo")
            ->orderBy('a.nombres')
            ->get();
    }

    private function relationshipOptions(): array
    {
        return ['Padre', 'Madre', 'Tio', 'Tia', 'Hermano', 'Hermana', 'Abuelo', 'Abuela', 'Encargado', 'Otro'];
    }

    private function guardianForEdit(int $guardianId): ?Guardian
    {
        $guardian = Guardian::active()->find($guardianId);

        if (! $guardian) {
            return null;
        }

        $guardian->setRelation('students', $guardian->students()->orderBy('padre_alumno.alumno_id')->get());

        return $guardian;
    }

    private function usersData()
    {
        return DB::table('usuarios as u')
            ->where('u.activo', true)
            ->join('roles as r', 'r.id', '=', 'u.rol_id')
            ->select('u.id', 'u.rol_id', 'u.nombre_usuario', 'u.email', 'u.nombres', 'u.apellidos', 'u.activo', 'r.nombre as rol')
            ->orderBy('u.id')
            ->get();
    }

    private function profilesData()
    {
        return DB::table('roles as r')
            ->where('r.activo', true)
            ->leftJoin('usuarios as u', function ($join) {
                $join->on('u.rol_id', '=', 'r.id')->where('u.activo', true);
            })
            ->leftJoin('rol_menu as rm', 'rm.rol_id', '=', 'r.id')
            ->leftJoin('menus as m', function ($join) {
                $join->on('m.id', '=', 'rm.menu_id')->where('m.activo', true);
            })
            ->groupBy('r.id', 'r.nombre', 'r.descripcion')
            ->selectRaw("r.id, r.nombre, r.descripcion, COUNT(DISTINCT u.id) as total_usuarios, COUNT(DISTINCT m.id) as total_permisos, GROUP_CONCAT(DISTINCT m.nombre ORDER BY m.orden SEPARATOR ' | ') as permisos")
            ->orderBy('r.nombre')
            ->get();
    }

    private function profileForEdit(int $roleId): ?Role
    {
        $role = Role::active()->find($roleId);

        if (! $role) {
            return null;
        }

        $role->setRelation('menus', $role->menus()->orderBy('orden')->get());

        return $role;
    }

    private function emailsData()
    {
        return DB::table('envios_correo as ec')
            ->where('ec.activo', true)
            ->join('plantillas_correo as pc', 'pc.id', '=', 'ec.plantilla_id')
            ->join('padres as p', function ($join) {
                $join->on('p.id', '=', 'ec.padre_id')->where('p.activo', true);
            })
            ->join('alumnos as a', function ($join) {
                $join->on('a.id', '=', 'ec.alumno_id')->where('a.activo', true);
            })
            ->join('trimestres as t', 't.id', '=', 'ec.trimestre_id')
            ->leftJoin('padre_alumno as pa', function ($join) {
                $join->on('pa.padre_id', '=', 'p.id')->on('pa.alumno_id', '=', 'a.id');
            })
            ->select('ec.id', 'ec.plantilla_id', 'ec.padre_id', 'ec.alumno_id', 'ec.trimestre_id', 'ec.estado', 'pc.nombre as plantilla', 'p.nombres', 'p.apellidos', 'p.email_principal', 'a.nombres as alumno_nombres', 'a.apellidos as alumno_apellidos', 't.nombre as trimestre', 'pa.parentesco')
            ->orderByDesc('ec.id')
            ->get();
    }

    private function notesData()
    {
        return DB::table('notas as n')
            ->where('n.activo', true)
            ->join('alumnos as a', function ($join) {
                $join->on('a.id', '=', 'n.alumno_id')->where('a.activo', true);
            })
            ->join('asignaciones as ag', 'ag.id', '=', 'n.asignacion_id')
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'ag.seccion_id')->where('s.activo', true);
            })
            ->join('trimestres as t', 't.id', '=', 'n.trimestre_id')
            ->join('categorias_evaluacion as c', 'c.id', '=', 'n.categoria_id')
            ->selectRaw("n.id, n.alumno_id, n.asignacion_id, n.trimestre_id, n.categoria_id, n.valor, CONCAT(a.nombres, ' ', a.apellidos) as alumno, CONCAT(m.nombre, ' - ', s.grado, ' ', s.nombre) as asignacion, t.nombre as trimestre, c.nombre as categoria")
            ->orderByDesc('n.id')
            ->get();
    }

    private function assignmentOptions()
    {
        return DB::table('asignaciones as ag')
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'ag.seccion_id')->where('s.activo', true);
            })
            ->join('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->selectRaw("ag.id, CONCAT(m.nombre, ' - ', s.grado, ' ', s.nombre, ' - ', p.nombres, ' ', p.apellidos) as etiqueta")
            ->orderBy('m.nombre')
            ->get();
    }

    private function audit()
    {
        return DB::table('auditoria_notas as an')
            ->join('usuarios as u', 'u.id', '=', 'an.usuario_id')
            ->join('notas as n', 'n.id', '=', 'an.nota_id')
            ->select('an.*', 'u.nombre_usuario', 'n.alumno_id')
            ->orderByDesc('an.id')
            ->limit(5)
            ->get();
    }

    private function buildGradeBoard(?int $assignmentId = null, int $trimesterId = 1): array
    {
        $assignments = $this->assignmentOptions();
        $selectedAssignmentId = $assignmentId ?: $assignments->first()?->id;
        $categories = DB::table('categorias_evaluacion')->orderBy('id')->get();

        if (! $selectedAssignmentId) {
            return [
                'assignment' => null,
                'categories' => collect(),
                'rows' => collect(),
                'selected_trimestre_id' => $trimesterId,
            ];
        }

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
            ->where('ag.id', $selectedAssignmentId)
            ->first();

        if (! $assignment) {
            return [
                'assignment' => null,
                'categories' => collect(),
                'rows' => collect(),
                'selected_trimestre_id' => $trimesterId,
            ];
        }

        $rows = DB::table('alumnos as a')
            ->where('a.activo', true)
            ->where('a.seccion_id', $assignment->seccion_id)
            ->leftJoin('notas as n', function ($join) use ($assignment, $trimesterId) {
                $join->on('n.alumno_id', '=', 'a.id')
                    ->where('n.asignacion_id', '=', $assignment->id)
                    ->where('n.trimestre_id', '=', $trimesterId)
                    ->where('n.activo', true);
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
            })
            ->values();

        return [
            'assignment' => $assignment,
            'categories' => $categories,
            'rows' => $rows,
            'selected_trimestre_id' => $trimesterId,
        ];
    }

    private function buildReportCard(array $filters = []): Collection
    {
        $query = DB::table('notas as n')
            ->where('n.activo', true)
            ->join('alumnos as a', function ($join) {
                $join->on('a.id', '=', 'n.alumno_id')->where('a.activo', true);
            })
            ->join('asignaciones as ag', 'ag.id', '=', 'n.asignacion_id')
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'a.seccion_id')->where('s.activo', true);
            })
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->selectRaw("a.id, s.id as seccion_id, CONCAT(a.nombres, ' ', a.apellidos) as alumno, CONCAT(s.grado, ' ', s.nombre) as seccion, m.nombre as materia, ROUND(AVG(n.valor), 1) as promedio")
            ->groupBy('a.id', 's.id', 'a.nombres', 'a.apellidos', 's.grado', 's.nombre', 'm.nombre')
            ->orderBy('a.id');

        if (! empty($filters['seccion_id'])) {
            $query->where('s.id', $filters['seccion_id']);
        }

        if (! empty($filters['alumno_id'])) {
            $query->where('a.id', $filters['alumno_id']);
        }

        if (! empty($filters['trimestre_id'])) {
            $query->where('n.trimestre_id', $filters['trimestre_id']);
        }

        return $query
            ->get()
            ->groupBy('id')
            ->map(function (Collection $studentRows) {
                $first = $studentRows->first();

                return [
                    'id' => $first->id,
                    'alumno' => $first->alumno,
                    'seccion' => $first->seccion,
                    'materias' => $studentRows->pluck('promedio', 'materia'),
                    'promedio' => round($studentRows->avg('promedio'), 1),
                ];
            })
            ->values();
    }

    private function subjectColumns(): array
    {
        return Subject::active()->orderBy('nombre')->pluck('nombre')->all();
    }
}
