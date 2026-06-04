@extends('layouts.panel')

@section('title', 'Alumnos')

@section('content')
@php($formVisible = $editStudent !== null || $errors->any())

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-user-graduate"></i>
                <span>Lista de alumnos</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#studentFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editStudent ? 'Editar alumno' : 'Nuevo alumno' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="students-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Nombre, apellido o seccion" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Seccion</label>
                <select class="form-control" data-filter-name="section">
                    <option value="">Todas</option>
                    @foreach ($sections as $section)
                        <option value="{{ strtolower($section->grado.' '.$section->nombre) }}">{{ $section->grado }} {{ $section->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="students-table">
                <thead class="bg-light"><tr><th>#</th><th>Estudiante</th><th>Seccion</th><th>Familiares</th><th>Promedio</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                @forelse ($students as $student)
                    <tr data-filter-row data-text="{{ strtolower($student->nombres.' '.$student->apellidos.' '.$student->grado.' '.$student->seccion_nombre) }}" data-section="{{ strtolower($student->grado.' '.$student->seccion_nombre) }}">
                        <td>{{ $student->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($student->nombres, 0, 1).substr($student->apellidos, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $student->nombres }} {{ $student->apellidos }}</strong>
                                    <div class="small text-muted">Promedio: {{ $student->promedio ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-info">{{ $student->grado }} {{ $student->seccion_nombre }}</span></td>
                        <td>{{ $student->total_padres }}</td>
                        <td>{{ $student->promedio ?? '-' }}</td>
                        <td><span class="maint-status maint-status-active">Activo</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info student-view-button" data-name="{{ $student->nombres }} {{ $student->apellidos }}" data-section="{{ $student->grado }} {{ $student->seccion_nombre }}" data-family="{{ $student->total_padres }}" data-average="{{ $student->promedio ?? '-' }}"><i class="fas fa-eye"></i></button>
                            <a href="{{ \App\Support\AppUrl::route('students.index') }}?edit_student={{ $student->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('students.destroy', ['student' => $student->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar alumno" data-swal-text="El alumno quedara inactivo y no se eliminara fisicamente de la base de datos." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No hay alumnos registrados.</td></tr>
                @endforelse
                @if ($students->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="7" class="text-center text-muted">No se encontraron alumnos con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="studentFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">{{ $editStudent ? 'Editar alumno' : 'Nuevo alumno' }}</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <form method="POST" action="{{ $editStudent ? \App\Support\AppUrl::route('students.update', ['student' => $editStudent->id]) : \App\Support\AppUrl::route('students.store') }}">
                    @csrf
                    @if ($editStudent) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Seccion</label><select name="seccion_id" class="form-control" required>@foreach ($sections as $section)<option value="{{ $section->id }}" @selected(old('seccion_id', $editStudent->seccion_id ?? '') == $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>@endforeach</select></div>
                        <div class="col-md-4 form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editStudent->nombres ?? '') }}" required></div>
                        <div class="col-md-4 form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editStudent->apellidos ?? '') }}" required></div>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editStudent ? 'Guardar cambios' : 'Agregar alumno' }}</button>
                    @if ($editStudent)<a href="{{ \App\Support\AppUrl::route('students.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="studentViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Detalle de alumno</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Nombre:</strong> <span data-student-field="name"></span></li>
                    <li><strong>Seccion:</strong> <span data-student-field="section"></span></li>
                    <li><strong>Familiares:</strong> <span data-student-field="family"></span></li>
                    <li><strong>Promedio:</strong> <span data-student-field="average"></span></li>
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
        $('#studentFormModal').modal('show');
        @endif

        document.querySelectorAll('.student-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-student-field="name"]').textContent = button.dataset.name;
                document.querySelector('[data-student-field="section"]').textContent = button.dataset.section;
                document.querySelector('[data-student-field="family"]').textContent = button.dataset.family;
                document.querySelector('[data-student-field="average"]').textContent = button.dataset.average;
                $('#studentViewModal').modal('show');
            });
        });
    });
</script>
@endpush
