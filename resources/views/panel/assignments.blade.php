@extends('layouts.panel')

@section('title', 'Asignaciones')

@section('content')
@php($formVisible = $canManageAcademicData && ($editAssignment !== null || $errors->any()))
@php($selectedAssignmentStudents = collect(old('alumno_ids', $editAssignment?->students?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all())

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-project-diagram"></i>
                <span>Asignacion de materias por seccion</span>
            </div>
            <div class="maint-actions">
                @if ($canManageAcademicData)<button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#assignmentFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editAssignment ? 'Editar asignacion' : 'Nueva asignacion' }}
                </button>@endif
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="assignments-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Seccion, materia o profesor" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Ano</label>
                <select class="form-control" data-filter-name="year">
                    <option value="">Todos</option>
                    @foreach ($assignmentYears as $year)
                        <option value="{{ strtolower((string) $year) }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="assignments-table">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Ano</th>
                    <th>Seccion</th>
                    <th>Materia</th>
                    <th>Profesor</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($assignments as $assignment)
                    <tr data-filter-row data-text="{{ strtolower($assignment->seccion.' '.$assignment->materia.' '.$assignment->profesor) }}" data-year="{{ strtolower((string) $assignment->anio_escolar) }}">
                        <td>{{ $assignment->id }}</td>
                        <td>{{ $assignment->anio_escolar }}</td>
                        <td><strong>{{ $assignment->es_electiva ? 'Varias secciones' : $assignment->seccion }}</strong></td>
                        <td>{{ $assignment->materia }} @if ($assignment->es_electiva)<span class="badge badge-info ml-1">Electiva · {{ $assignment->total_alumnos_electivos }} alumnos</span>@endif</td>
                        <td>{{ $assignment->profesor }}</td>
                        <td><span class="maint-status maint-status-active">Activa</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info assignment-view-button"
                                data-year="{{ $assignment->anio_escolar }}"
                                data-section="{{ $assignment->seccion }}"
                                data-subject="{{ $assignment->materia }}"
                                data-teacher="{{ $assignment->profesor }}"
                                data-students="{{ $assignment->es_electiva ? $assignment->total_alumnos_electivos.' alumnos seleccionados' : 'Toda la sección' }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if ($canManageAcademicData)<a href="{{ \App\Support\AppUrl::route('assignments.index') }}?edit_assignment={{ $assignment->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('assignments.destroy', ['assignment' => $assignment->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar asignacion" data-swal-text="La asignacion dejara de usarse en procesos nuevos, pero el historial seguira disponible." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No hay asignaciones activas registradas.</td></tr>
                @endforelse
                @if ($assignments->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="7" class="text-center text-muted">No se encontraron asignaciones con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="assignmentFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editAssignment ? 'Editar asignacion' : 'Nueva asignacion' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editAssignment ? \App\Support\AppUrl::route('assignments.update', ['assignment' => $editAssignment->id]) : \App\Support\AppUrl::route('assignments.store') }}">
                    @csrf
                    @if ($editAssignment) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Ano lectivo</label>
                            <input type="number" min="2000" max="2100" name="anio_escolar" id="assignment-year-input" class="form-control" value="{{ old('anio_escolar', $editAssignment->anio_escolar ?? ($assignmentYears->first() ?? now()->year)) }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Sección base</label>
                            <select name="seccion_id" id="assignment-section-select" class="form-control" required>
                                <option value="">Seleccione una seccion</option>
                                @foreach ($sectionsCatalog as $section)
                                    <option value="{{ $section->id }}" data-year="{{ $section->anio_escolar }}" @selected(old('seccion_id', $editAssignment->seccion_id ?? '') == $section->id)>{{ $section->grado }} {{ $section->nombre }} | {{ $section->anio_escolar }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">En materias regulares se incluye toda la sección. En electivas sirve como referencia.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Materia</label>
                            <select name="materia_id" id="assignment-subject-select" class="form-control" required>
                                <option value="">Seleccione una materia</option>
                                @foreach ($subjectsCatalog as $subject)
                                    <option value="{{ $subject->id }}" data-elective="{{ $subject->es_electiva ? '1' : '0' }}" @selected(old('materia_id', $editAssignment->materia_id ?? '') == $subject->id)>{{ $subject->nombre }}{{ $subject->es_electiva ? ' (Electiva)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group" id="assignment-elective-students" style="display:none;">
                            <label>Alumnos de la electiva</label>
                            <select name="alumno_ids[]" id="assignment-students-select" class="form-control" multiple size="10">
                                @foreach ($assignmentStudentsCatalog as $student)
                                    <option value="{{ $student->id }}" data-year="{{ $student->anio_escolar }}" @selected(in_array((int) $student->id, $selectedAssignmentStudents, true))>{{ $student->seccion }} · {{ $student->apellidos }}, {{ $student->nombres }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Usa Ctrl (Windows) o Cmd (Mac) para seleccionar estudiantes de varias secciones del mismo año.</small>
                            @error('alumno_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Profesor</label>
                            <select name="profesor_id" class="form-control" required>
                                <option value="">Seleccione un profesor</option>
                                @foreach ($teachersCatalog as $teacher)
                                    <option value="{{ $teacher->id }}" @selected(old('profesor_id', $editAssignment->profesor_id ?? '') == $teacher->id)>{{ $teacher->nombres }} {{ $teacher->apellidos }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Si necesitas validar afinidad, la especialidad del docente se administra en el catalogo de profesores.</small>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editAssignment ? 'Guardar cambios' : 'Agregar asignacion' }}</button>
                    @if ($editAssignment)<a href="{{ \App\Support\AppUrl::route('assignments.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignmentViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de asignacion</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Ano:</strong> <span data-assignment-field="year"></span></li>
                    <li><strong>Seccion:</strong> <span data-assignment-field="section"></span></li>
                    <li><strong>Materia:</strong> <span data-assignment-field="subject"></span></li>
                    <li><strong>Profesor:</strong> <span data-assignment-field="teacher"></span></li>
                    <li><strong>Alumnos:</strong> <span data-assignment-field="students"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if ($formVisible)
        $('#assignmentFormModal').modal('show');
        @endif

        const sectionSelect = document.getElementById('assignment-section-select');
        const yearInput = document.getElementById('assignment-year-input');
        const subjectSelect = document.getElementById('assignment-subject-select');
        const electiveStudents = document.getElementById('assignment-elective-students');
        const studentsSelect = document.getElementById('assignment-students-select');

        if (sectionSelect && yearInput) {
            const syncYearFromSection = function () {
                const selectedOption = sectionSelect.options[sectionSelect.selectedIndex];

                if (!selectedOption || !selectedOption.dataset.year) {
                    return;
                }

                yearInput.value = selectedOption.dataset.year;
            };

            sectionSelect.addEventListener('change', syncYearFromSection);
            syncYearFromSection();
        }

        const syncElectiveFields = function () {
            const subjectOption = subjectSelect?.options[subjectSelect.selectedIndex];
            const isElective = subjectOption?.dataset.elective === '1';

            if (electiveStudents) electiveStudents.style.display = isElective ? '' : 'none';
            if (studentsSelect) {
                studentsSelect.required = isElective;
                Array.from(studentsSelect.options).forEach(function (option) {
                    option.hidden = Boolean(yearInput?.value) && option.dataset.year !== yearInput.value;
                    if (option.hidden && !option.selected) option.disabled = true;
                    else option.disabled = false;
                });
            }
        };

        subjectSelect?.addEventListener('change', syncElectiveFields);
        yearInput?.addEventListener('change', syncElectiveFields);
        sectionSelect?.addEventListener('change', syncElectiveFields);
        syncElectiveFields();

        document.querySelectorAll('.assignment-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-assignment-field="year"]').textContent = button.dataset.year;
                document.querySelector('[data-assignment-field="section"]').textContent = button.dataset.section;
                document.querySelector('[data-assignment-field="subject"]').textContent = button.dataset.subject;
                document.querySelector('[data-assignment-field="teacher"]').textContent = button.dataset.teacher;
                document.querySelector('[data-assignment-field="students"]').textContent = button.dataset.students;
                $('#assignmentViewModal').modal('show');
            });
        });
    });
</script>
@endpush
