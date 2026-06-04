<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Assignment;
use App\Models\EmailDispatch;
use App\Models\EmailTemplate;
use App\Models\CollectorCategory;
use App\Models\Guardian;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SystemBackup;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
            'subjectsCatalog' => Subject::active()->orderBy('nombre')->get(),
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

    public function assignments(): View
    {
        return view('panel.assignments', [
            ...$this->baseData(),
            'activeMenu' => 'assignments',
            'assignments' => $this->assignmentsData(),
            'sectionsCatalog' => Section::active()->orderBy('anio_escolar', 'desc')->orderBy('grado')->orderBy('nombre')->get(),
            'subjectsCatalog' => Subject::active()->orderBy('nombre')->get(),
            'teachersCatalog' => Teacher::active()->orderBy('nombres')->orderBy('apellidos')->get(),
            'assignmentYears' => $this->assignmentYears(),
            'editAssignment' => request()->filled('edit_assignment') ? Assignment::active()->find(request()->integer('edit_assignment')) : null,
        ]);
    }

    public function guardians(): View
    {
        return view('panel.guardians', [
            ...$this->baseData(),
            'activeMenu' => 'guardians',
            'parents' => $this->guardiansData(),
            'studentsForFamily' => $this->studentsForFamily(),
            'studentSections' => Section::active()->orderBy('grado')->orderBy('nombre')->get(['id', 'grado', 'nombre']),
            'relationshipOptions' => $this->relationshipOptions(),
            'editGuardian' => request()->filled('edit_guardian') ? $this->guardianForEdit(request()->integer('edit_guardian')) : null,
        ]);
    }

    public function gradeBook(): View
    {
        $academicYears = $this->academicYears();
        $selectedYear = request()->integer('anio_escolar') ?: $academicYears->first();
        $sections = $this->sectionsForYear($selectedYear);
        $selectedSectionId = request()->integer('seccion_id') ?: $sections->first()?->id;
        $subjects = $this->subjectsForFilters($selectedYear, $selectedSectionId);
        $selectedSubjectId = request()->integer('materia_id') ?: $subjects->first()?->id;
        $selectedTrimesterId = request()->integer('trimestre_id', 1);
        $selectedAssignment = $this->resolveAssignment($selectedYear, $selectedSectionId, $selectedSubjectId);
        $selectedAssignmentId = $selectedAssignment?->id;
        $categories = $this->collectorCategoriesData($selectedAssignmentId, $selectedTrimesterId);
        $gradeBoard = $this->buildGradeBoard($selectedAssignmentId, $selectedTrimesterId);

        return view('panel.gradebook', [
            ...$this->baseData(),
            'activeMenu' => 'gradebook',
            'academicYears' => $academicYears,
            'sections' => $sections,
            'subjects' => $subjects,
            'trimesters' => DB::table('trimestres')->orderBy('numero')->get(),
            'selectedYear' => $selectedYear,
            'selectedSectionId' => $selectedSectionId,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedAssignmentId' => $selectedAssignmentId,
            'selectedTrimesterId' => $selectedTrimesterId,
            'categories' => $categories,
            'selectedAssignment' => $selectedAssignment,
            'editCategory' => request()->filled('edit_category') ? CollectorCategory::active()->find(request()->integer('edit_category')) : null,
            'gradeBoard' => $gradeBoard,
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

    public function reportCardPdf(): Response|RedirectResponse
    {
        $studentId = request()->integer('alumno_id');
        $sectionId = request()->integer('seccion_id');

        if (! $studentId && ! $sectionId) {
            return redirect()
                ->route('reportcard.index', request()->query())
                ->with('error', 'Selecciona una seccion o un alumno para descargar el boletin anual.');
        }

        $reports = $studentId
            ? collect([$this->buildAnnualStudentReport($studentId, $sectionId)])->filter()
            : $this->buildAnnualSectionReports($sectionId);

        if ($reports->isEmpty()) {
            return redirect()
                ->route('reportcard.index', request()->query())
                ->with('error', 'No se encontro informacion suficiente para generar el PDF.');
        }

        $firstReport = $reports->first();
        $fileName = $studentId
            ? 'boletin-'.$firstReport['student']['id'].'-'.$this->safeFileName($firstReport['student']['full_name']).'.pdf'
            : 'boletines-seccion-'.$firstReport['student']['section_id'].'-'.$this->safeFileName($firstReport['student']['section_name']).'.pdf';

        return Pdf::loadView('panel.reportcard-pdf', [
            'reports' => $reports,
        ])->setPaper('legal', 'landscape')->download($fileName);
    }

    public function emails(): View
    {
        return view('panel.emails', [
            ...$this->baseData(),
            'activeMenu' => 'emails',
            'emails' => $this->emailsData(),
            'templates' => EmailTemplate::active()->orderBy('nombre')->get(),
            'templateCatalog' => EmailTemplate::active()->orderBy('nombre')->get(),
            'familyMembers' => Guardian::active()->orderBy('nombres')->orderBy('apellidos')->get(),
            'studentsForEmail' => $this->studentsForFamily(),
            'studentSections' => Section::active()->orderBy('grado')->orderBy('nombre')->get(['id', 'grado', 'nombre']),
            'trimesters' => DB::table('trimestres')->orderBy('numero')->get(),
            'editEmail' => request()->filled('edit_email') ? EmailDispatch::active()->find(request()->integer('edit_email')) : null,
            'editTemplate' => request()->filled('edit_template') ? EmailTemplate::active()->find(request()->integer('edit_template')) : null,
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

    public function menus(): View
    {
        return view('panel.menus', [
            ...$this->baseData(),
            'activeMenu' => 'menus',
            'menus' => Menu::active()->orderBy('orden')->orderBy('id')->get(),
            'editMenu' => request()->filled('edit_menu') ? Menu::active()->find(request()->integer('edit_menu')) : null,
        ]);
    }

    public function config(): View
    {
        return view('panel.config', [
            ...$this->baseData(),
            'activeMenu' => 'config',
        ]);
    }

    public function backups(): View
    {
        return view('panel.backups', [
            ...$this->baseData(),
            'activeMenu' => 'backups',
            'backups' => $this->backupsData(),
            'hasPendingBackups' => SystemBackup::query()->whereIn('estado', ['pendiente', 'procesando'])->exists(),
        ]);
    }

    private function baseData(): array
    {
        return [
            'stats' => [
                'alumnos' => DB::table('alumnos')->where('activo', true)->count(),
                'secciones' => DB::table('secciones')->where('activo', true)->count(),
                'profesores' => DB::table('profesores')->where('activo', true)->count(),
                'notas' => DB::table('notas_alumnos')->where('activo', true)->count(),
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
            $allowedKeys = collect($user->allowedMenuKeys());
            $allowedParentIds = $items
                ->whereIn('clave', $allowedKeys)
                ->pluck('parent_id')
                ->filter()
                ->all();

            $items = $items
                ->filter(fn (Menu $item) => $allowedKeys->contains($item->clave) || in_array($item->id, $allowedParentIds, true))
                ->values();
        }

        if ($items->isEmpty()) {
            return [];
        }

        $childrenByParent = $items
            ->whereNotNull('parent_id')
            ->groupBy('parent_id');

        return $items
            ->whereNull('parent_id')
            ->mapWithKeys(function (Menu $item) use ($childrenByParent) {
                $children = ($childrenByParent[$item->id] ?? collect())
                    ->map(fn (Menu $child) => [
                        'key' => $child->clave,
                        'label' => $child->nombre,
                        'icon' => $child->icono,
                        'url' => $child->resolved_url,
                    ])
                    ->values()
                    ->all();

                return [
                    $item->clave => [
                        'label' => $item->nombre,
                        'icon' => $item->icono,
                        'url' => $item->resolved_url,
                        'children' => $children,
                    ],
                ];
            })
            ->all();
    }

    private function sectionsData()
    {
        $finals = $this->subjectFinalsSubquery();

        return DB::table('secciones as s')
            ->where('s.activo', true)
            ->leftJoin('alumnos as a', function ($join) {
                $join->on('a.seccion_id', '=', 's.id')->where('a.activo', true);
            })
            ->leftJoin('asignaciones as ag', function ($join) {
                $join->on('ag.seccion_id', '=', 's.id')->where('ag.activo', true);
            })
            ->leftJoinSub($finals, 'sf', function ($join) {
                $join->on('sf.asignacion_id', '=', 'ag.id')->on('sf.alumno_id', '=', 'a.id');
            })
            ->groupBy('s.id', 's.nombre', 's.grado', 's.anio_escolar')
            ->selectRaw('s.id, s.nombre, s.grado, s.anio_escolar, COUNT(DISTINCT a.id) as total_alumnos, COUNT(DISTINCT ag.materia_id) as total_materias, ROUND(AVG(sf.nota_final), 1) as promedio')
            ->orderBy('s.id')
            ->get();
    }

    private function studentsData()
    {
        $finals = $this->subjectFinalsSubquery();

        return DB::table('alumnos as a')
            ->where('a.activo', true)
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'a.seccion_id')->where('s.activo', true);
            })
            ->leftJoin('padre_alumno as pa', 'pa.alumno_id', '=', 'a.id')
            ->leftJoinSub($finals, 'sf', function ($join) {
                $join->on('sf.alumno_id', '=', 'a.id');
            })
            ->groupBy('a.id', 'a.seccion_id', 'a.nombres', 'a.apellidos', 's.grado', 's.nombre')
            ->selectRaw('a.id, a.seccion_id, a.nombres, a.apellidos, s.grado, s.nombre as seccion_nombre, COUNT(DISTINCT pa.padre_id) as total_padres, ROUND(AVG(sf.nota_final), 1) as promedio')
            ->orderBy('a.id')
            ->get();
    }

    private function teachersData()
    {
        return DB::table('profesores as p')
            ->where('p.activo', true)
            ->leftJoin('asignaciones as ag', function ($join) {
                $join->on('ag.profesor_id', '=', 'p.id')->where('ag.activo', true);
            })
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
        $finals = $this->subjectFinalsSubquery();

        return DB::table('materias as m')
            ->where('m.activo', true)
            ->leftJoin('asignaciones as ag', function ($join) {
                $join->on('ag.materia_id', '=', 'm.id')->where('ag.activo', true);
            })
            ->leftJoin('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->leftJoinSub($finals, 'sf', function ($join) {
                $join->on('sf.asignacion_id', '=', 'ag.id');
            })
            ->groupBy('m.id', 'm.nombre')
            ->selectRaw('m.id, m.nombre, COUNT(DISTINCT p.id) as total_profesores, COUNT(DISTINCT ag.seccion_id) as total_secciones, ROUND(AVG(sf.nota_final), 1) as promedio')
            ->orderBy('m.id')
            ->get();
    }

    private function assignmentsData()
    {
        return DB::table('asignaciones as ag')
            ->where('ag.activo', true)
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'ag.seccion_id')->where('s.activo', true);
            })
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->join('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->selectRaw("ag.id, ag.anio_escolar, CONCAT(s.grado, ' ', s.nombre) as seccion, m.nombre as materia, TRIM(CONCAT(p.nombres, ' ', p.apellidos)) as profesor")
            ->orderByDesc('ag.anio_escolar')
            ->orderBy('s.grado')
            ->orderBy('s.nombre')
            ->orderBy('m.nombre')
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
            ->selectRaw("a.id, a.seccion_id, CONCAT(a.nombres, ' ', a.apellidos, ' - ', s.grado, ' ', s.nombre) as nombre_completo")
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
            ->join('plantillas_correo as pc', function ($join) {
                $join->on('pc.id', '=', 'ec.plantilla_id')->where('pc.activo', true);
            })
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

    private function collectorCategoriesData(?int $assignmentId, int $trimesterId)
    {
        if (! $assignmentId) {
            return collect();
        }

        return DB::table('categorias_evaluacion')
            ->where('activo', true)
            ->where('asignacion_id', $assignmentId)
            ->where('trimestre_id', $trimesterId)
            ->select('id', 'asignacion_id', 'trimestre_id', 'nombre', 'porcentaje', 'tipo_calculo', 'cantidad_notas', 'orden')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    private function academicYears()
    {
        return DB::table('asignaciones')
            ->where('activo', true)
            ->select('anio_escolar')
            ->distinct()
            ->orderByDesc('anio_escolar')
            ->pluck('anio_escolar');
    }

    private function assignmentYears()
    {
        return Assignment::active()
            ->select('anio_escolar')
            ->distinct()
            ->orderByDesc('anio_escolar')
            ->pluck('anio_escolar');
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

    private function backupsData()
    {
        return SystemBackup::query()
            ->latestFirst()
            ->with('user:id,nombre_usuario,nombres,apellidos')
            ->get();
    }

    private function buildGradeBoard(?int $assignmentId, int $trimesterId): array
    {
        if (! $assignmentId) {
            return [
                'assignment' => null,
                'categories' => collect(),
                'rows' => collect(),
                'percentage_total' => 0,
                'can_calculate_report' => false,
            ];
        }

        $assignment = DB::table('asignaciones as ag')
            ->where('ag.activo', true)
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
            ->where('ag.id', $assignmentId)
            ->first();

        $categories = $this->collectorCategoriesData($assignmentId, $trimesterId);

        if (! $assignment) {
            return [
                'assignment' => null,
                'categories' => collect(),
                'rows' => collect(),
                'percentage_total' => 0,
                'can_calculate_report' => false,
            ];
        }

        $students = DB::table('alumnos')
            ->where('activo', true)
            ->where('seccion_id', $assignment->seccion_id)
            ->select('id', 'nombres', 'apellidos')
            ->orderBy('id')
            ->get();

        $scores = DB::table('notas_alumnos')
            ->where('activo', true)
            ->whereIn('categoria_id', $categories->pluck('id'))
            ->select('alumno_id', 'categoria_id', 'nota_1', 'nota_2', 'nota_3', 'nota_4', 'promedio_1', 'promedio_2')
            ->get()
            ->groupBy('alumno_id')
            ->map(fn (Collection $rows) => $rows->keyBy('categoria_id'));

        $conducts = DB::table('conducta_alumnos')
            ->where('activo', true)
            ->where('asignacion_id', $assignmentId)
            ->where('trimestre_id', $trimesterId)
            ->pluck('valor', 'alumno_id');

        $percentageTotal = round((float) $categories->sum('porcentaje'), 2);

        $rows = $students->map(function ($student) use ($categories, $scores, $conducts, $percentageTotal) {
            $studentScores = $scores->get($student->id, collect());
            $progress1 = 0;
            $progress2 = 0;
            $categoryRows = [];

            foreach ($categories as $category) {
                $score = $studentScores->get($category->id);
                $categoryRows[$category->id] = [
                    'nota_1' => $score->nota_1 ?? null,
                    'nota_2' => $score->nota_2 ?? null,
                    'nota_3' => $score->nota_3 ?? null,
                    'nota_4' => $score->nota_4 ?? null,
                    'promedio_1' => $score->promedio_1 ?? null,
                    'promedio_2' => $score->promedio_2 ?? null,
                ];

                $progress1 += (float) ($score->promedio_1 ?? 0);
                $progress2 += (float) ($score->promedio_2 ?? 0);
            }

            return [
                'id' => $student->id,
                'nombre' => trim($student->nombres.' '.$student->apellidos),
                'categories' => $categoryRows,
                'progress_1' => round($progress1, 2),
                'progress_2' => round($progress2, 2),
                'report_card' => $percentageTotal === 100.0 ? round(($progress1 + $progress2) / 2, 2) : null,
                'conducta' => $conducts[$student->id] ?? null,
            ];
        });

        return [
            'assignment' => $assignment,
            'categories' => $categories,
            'rows' => $rows,
            'percentage_total' => $percentageTotal,
            'can_calculate_report' => $percentageTotal === 100.0,
        ];
    }

    private function buildReportCard(array $filters = []): Collection
    {
        $finals = $this->subjectFinalsSubquery();

        $query = DB::query()
            ->fromSub($finals, 'sf')
            ->join('alumnos as a', function ($join) {
                $join->on('a.id', '=', 'sf.alumno_id')->where('a.activo', true);
            })
            ->join('asignaciones as ag', function ($join) {
                $join->on('ag.id', '=', 'sf.asignacion_id')->where('ag.activo', true);
            })
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'a.seccion_id')->where('s.activo', true);
            })
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->selectRaw("a.id, s.id as seccion_id, CONCAT(a.nombres, ' ', a.apellidos) as alumno, CONCAT(s.grado, ' ', s.nombre) as seccion, m.nombre as materia, ROUND(AVG(sf.nota_final), 2) as promedio")
            ->groupBy('a.id', 's.id', 'a.nombres', 'a.apellidos', 's.grado', 's.nombre', 'm.nombre')
            ->orderBy('a.id');

        if (! empty($filters['seccion_id'])) {
            $query->where('s.id', $filters['seccion_id']);
        }

        if (! empty($filters['alumno_id'])) {
            $query->where('a.id', $filters['alumno_id']);
        }

        if (! empty($filters['trimestre_id'])) {
            $query->where('sf.trimestre_id', $filters['trimestre_id']);
        }

        return $query->get()
            ->groupBy('id')
            ->map(function (Collection $studentRows) {
                $first = $studentRows->first();

                return [
                    'id' => $first->id,
                    'alumno' => $first->alumno,
                    'seccion' => $first->seccion,
                    'materias' => $studentRows->pluck('promedio', 'materia'),
                    'promedio' => round($studentRows->avg('promedio'), 2),
                ];
            })
            ->values();
    }

    private function subjectColumns(): array
    {
        return Subject::active()->orderBy('nombre')->pluck('nombre')->all();
    }

    private function buildAnnualStudentReport(int $studentId, ?int $sectionId = null): ?array
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

        $subjects = $assignments->map(function ($assignment) use ($trimesters, $finalRows, $conductRows) {
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

            return [
                'subject' => $assignment->subject_name,
                'teacher' => $assignment->teacher_name ?: 'Sin asignar',
                'terms' => $trimesterData,
                'semester_evaluation' => $this->averageValues([
                    $trimesterData[1]['period_average'] ?? null,
                    $trimesterData[2]['period_average'] ?? null,
                ]),
                'final_evaluation' => $this->averageValues([
                    $trimesterData[3]['period_average'] ?? null,
                    $trimesterData[4]['period_average'] ?? null,
                ]),
                'final_conduct' => $this->averageValues($conductAverages),
                'final_period' => $this->averageValues($periodAverages),
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

    private function buildAnnualSectionReports(?int $sectionId): Collection
    {
        if (! $sectionId) {
            return collect();
        }

        $studentIds = DB::table('alumnos')
            ->where('activo', true)
            ->where('seccion_id', $sectionId)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->pluck('id');

        return $studentIds
            ->map(fn ($studentId) => $this->buildAnnualStudentReport((int) $studentId, $sectionId))
            ->filter()
            ->values();
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

    private function safeFileName(string $value): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', trim($value));
        $slug = trim((string) $slug, '-');

        return $slug !== '' ? strtolower($slug) : 'reporte';
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
            ->joinSub($totals, 'ct', function ($join) {
                $join->on('ct.asignacion_id', '=', 'c.asignacion_id')
                    ->on('ct.trimestre_id', '=', 'c.trimestre_id');
            })
            ->selectRaw('na.alumno_id, c.asignacion_id, c.trimestre_id, ROUND(SUM(COALESCE(na.promedio_1, 0)), 2) as progress_1, ROUND(SUM(COALESCE(na.promedio_2, 0)), 2) as progress_2, CASE WHEN ct.porcentaje_total = 100 THEN ROUND((SUM(COALESCE(na.promedio_1, 0)) + SUM(COALESCE(na.promedio_2, 0))) / 2, 2) ELSE NULL END as nota_final')
            ->groupBy('na.alumno_id', 'c.asignacion_id', 'c.trimestre_id', 'ct.porcentaje_total');
    }

    private function sectionsForYear(?int $year)
    {
        return DB::table('secciones')
            ->where('activo', true)
            ->when($year, fn ($query) => $query->where('anio_escolar', $year))
            ->orderBy('grado')
            ->orderBy('nombre')
            ->get(['id', 'grado', 'nombre', 'anio_escolar']);
    }

    private function subjectsForFilters(?int $year, ?int $sectionId)
    {
        return DB::table('asignaciones as ag')
            ->where('ag.activo', true)
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->when($year, fn ($query) => $query->where('ag.anio_escolar', $year))
            ->when($sectionId, fn ($query) => $query->where('ag.seccion_id', $sectionId))
            ->select('m.id', 'm.nombre')
            ->distinct()
            ->orderBy('m.nombre')
            ->get();
    }

    private function resolveAssignment(?int $year, ?int $sectionId, ?int $subjectId): ?object
    {
        if (! $year || ! $sectionId || ! $subjectId) {
            return null;
        }

        return DB::table('asignaciones as ag')
            ->where('ag.activo', true)
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'ag.materia_id')->where('m.activo', true);
            })
            ->join('secciones as s', function ($join) {
                $join->on('s.id', '=', 'ag.seccion_id')->where('s.activo', true);
            })
            ->join('profesores as p', function ($join) {
                $join->on('p.id', '=', 'ag.profesor_id')->where('p.activo', true);
            })
            ->where('ag.anio_escolar', $year)
            ->where('ag.seccion_id', $sectionId)
            ->where('ag.materia_id', $subjectId)
            ->select('ag.id', 'ag.seccion_id', 'ag.materia_id', 'ag.anio_escolar', 'm.nombre as materia', 's.grado', 's.nombre as seccion', DB::raw("CONCAT(p.nombres, ' ', p.apellidos) as profesor"))
            ->orderBy('ag.id')
            ->first();
    }
}
