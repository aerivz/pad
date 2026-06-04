@extends('layouts.panel')

@section('title', 'Profesores')

@section('content')
@php($formVisible = $editTeacher !== null || $errors->any())

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Lista de profesores</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#teacherFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editTeacher ? 'Editar profesor' : 'Nuevo profesor' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="teachers-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Nombre, correo o especialidad" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Especialidad</label>
                <select class="form-control" data-filter-name="specialty">
                    <option value="">Todas</option>
                    @foreach ($teachers->pluck('especialidad')->filter()->unique()->sort() as $specialty)
                        <option value="{{ strtolower($specialty) }}">{{ $specialty }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="teachers-table">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Profesor</th>
                    <th>Correo</th>
                    <th>Especialidad</th>
                    <th>Secciones</th>
                    <th>Asignaciones</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($teachers as $teacher)
                    <tr data-filter-row data-text="{{ strtolower($teacher->nombres.' '.$teacher->apellidos.' '.$teacher->email.' '.$teacher->especialidad.' '.$teacher->materias) }}" data-specialty="{{ strtolower($teacher->especialidad ?? 'sin especialidad') }}">
                        <td>{{ $teacher->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($teacher->nombres, 0, 1).substr($teacher->apellidos, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $teacher->nombres }} {{ $teacher->apellidos }}</strong>
                                    <div class="small text-muted">{{ $teacher->materias ?: 'Sin materias asignadas' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $teacher->email }}</td>
                        <td>{{ $teacher->especialidad ?: 'Sin especialidad' }}</td>
                        <td>{{ $teacher->total_secciones }}</td>
                        <td>{{ $teacher->total_asignaciones }}</td>
                        <td><span class="maint-status maint-status-active">Activo</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info teacher-view-button"
                                data-name="{{ $teacher->nombres }} {{ $teacher->apellidos }}"
                                data-email="{{ $teacher->email }}"
                                data-specialty="{{ $teacher->especialidad ?: 'Sin especialidad' }}"
                                data-sections="{{ $teacher->total_secciones }}"
                                data-assignments="{{ $teacher->total_asignaciones }}"
                                data-subjects="{{ $teacher->materias ?: 'Sin materias asignadas' }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ \App\Support\AppUrl::route('teachers.index') }}?edit_teacher={{ $teacher->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('teachers.destroy', ['teacher' => $teacher->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar profesor" data-swal-text="El profesor quedara inactivo y sus datos seguiran resguardados en el sistema." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No hay profesores registrados.</td></tr>
                @endforelse
                @if ($teachers->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="8" class="text-center text-muted">No se encontraron profesores con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="teacherFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editTeacher ? 'Editar profesor' : 'Nuevo profesor' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editTeacher ? \App\Support\AppUrl::route('teachers.update', ['teacher' => $editTeacher->id]) : \App\Support\AppUrl::route('teachers.store') }}">
                    @csrf
                    @if ($editTeacher) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editTeacher->nombres ?? '') }}" required></div>
                        <div class="col-md-3 form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editTeacher->apellidos ?? '') }}" required></div>
                        <div class="col-md-3 form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $editTeacher->email ?? '') }}" required></div>
                        <div class="col-md-3 form-group">
                            @php($selectedSpecialty = old('especialidad', $editTeacher->especialidad ?? ''))
                            @php($specialtyList = collect($subjectsCatalog ?? [])->pluck('nombre')->filter()->unique())
                            @if ($selectedSpecialty && ! $specialtyList->contains($selectedSpecialty))
                                @php($specialtyList = $specialtyList->push($selectedSpecialty))
                            @endif
                            <label>Especialidad</label>
                            <select name="especialidad" class="form-control">
                                <option value="">Sin especialidad</option>
                                @foreach ($specialtyList->sort()->values() as $specialtyOption)
                                    <option value="{{ $specialtyOption }}" @selected($selectedSpecialty === $specialtyOption)>{{ $specialtyOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editTeacher ? 'Guardar cambios' : 'Agregar profesor' }}</button>
                    @if ($editTeacher)<a href="{{ \App\Support\AppUrl::route('teachers.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="teacherViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de profesor</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Nombre:</strong> <span data-teacher-field="name"></span></li>
                    <li><strong>Correo:</strong> <span data-teacher-field="email"></span></li>
                    <li><strong>Especialidad:</strong> <span data-teacher-field="specialty"></span></li>
                    <li><strong>Secciones:</strong> <span data-teacher-field="sections"></span></li>
                    <li><strong>Asignaciones:</strong> <span data-teacher-field="assignments"></span></li>
                    <li><strong>Materias:</strong> <span data-teacher-field="subjects"></span></li>
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
        $('#teacherFormModal').modal('show');
        @endif

        document.querySelectorAll('.teacher-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-teacher-field="name"]').textContent = button.dataset.name;
                document.querySelector('[data-teacher-field="email"]').textContent = button.dataset.email;
                document.querySelector('[data-teacher-field="specialty"]').textContent = button.dataset.specialty;
                document.querySelector('[data-teacher-field="sections"]').textContent = button.dataset.sections;
                document.querySelector('[data-teacher-field="assignments"]').textContent = button.dataset.assignments;
                document.querySelector('[data-teacher-field="subjects"]').textContent = button.dataset.subjects;
                $('#teacherViewModal').modal('show');
            });
        });
    });
</script>
@endpush
