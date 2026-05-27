@extends('layouts.panel')

@section('title', 'Asignaciones')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editAssignment ? 'Editar asignacion' : 'Nueva asignacion' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editAssignment ? \App\Support\AppUrl::route('assignments.update', ['assignment' => $editAssignment->id]) : \App\Support\AppUrl::route('assignments.store') }}">
                    @csrf
                    @if ($editAssignment) @method('PATCH') @endif
                    <div class="form-group">
                        <label>Año lectivo</label>
                        <input type="number" min="2000" max="2100" name="anio_escolar" class="form-control" value="{{ old('anio_escolar', $editAssignment->anio_escolar ?? ($assignmentYears->first() ?? now()->year)) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Seccion</label>
                        <select name="seccion_id" class="form-control" required>
                            <option value="">Seleccione una seccion</option>
                            @foreach ($sectionsCatalog as $section)
                                <option value="{{ $section->id }}" @selected(old('seccion_id', $editAssignment->seccion_id ?? '') == $section->id)>{{ $section->grado }} {{ $section->nombre }} | {{ $section->anio_escolar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Materia</label>
                        <select name="materia_id" class="form-control" required>
                            <option value="">Seleccione una materia</option>
                            @foreach ($subjectsCatalog as $subject)
                                <option value="{{ $subject->id }}" @selected(old('materia_id', $editAssignment->materia_id ?? '') == $subject->id)>{{ $subject->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Profesor</label>
                        <select name="profesor_id" class="form-control" required>
                            <option value="">Seleccione un profesor</option>
                            @foreach ($teachersCatalog as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('profesor_id', $editAssignment->profesor_id ?? '') == $teacher->id)>{{ $teacher->nombres }} {{ $teacher->apellidos }}{{ $teacher->especialidad ? ' | '.$teacher->especialidad : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editAssignment ? 'Guardar cambios' : 'Agregar asignacion' }}</button>
                    @if ($editAssignment)<a href="{{ \App\Support\AppUrl::route('assignments.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Asignaciones registradas</h3>
                <div class="card-tools w-100 mt-2 mt-md-0">
                    <div class="filter-toolbar justify-content-md-end" data-filter-target="assignments-table">
                        <div class="form-group">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Seccion, materia o profesor" data-filter-name="text">
                        </div>
                        <div class="form-group">
                            <label class="small text-muted mb-1">Año</label>
                            <select class="form-control form-control-sm" data-filter-name="year">
                                <option value="">Todos</option>
                                @foreach ($assignmentYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover" id="assignments-table">
                    <thead class="bg-light"><tr><th>#</th><th>Año</th><th>Seccion</th><th>Materia</th><th>Profesor</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($assignments as $assignment)
                        <tr data-filter-row data-text="{{ strtolower($assignment->seccion.' '.$assignment->materia.' '.$assignment->profesor) }}" data-year="{{ $assignment->anio_escolar }}">
                            <td>{{ $assignment->id }}</td>
                            <td>{{ $assignment->anio_escolar }}</td>
                            <td><strong>{{ $assignment->seccion }}</strong></td>
                            <td>{{ $assignment->materia }}</td>
                            <td>{{ $assignment->profesor }}</td>
                            <td class="action-cell">
                                <a href="{{ \App\Support\AppUrl::route('assignments.index') }}?edit_assignment={{ $assignment->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ \App\Support\AppUrl::route('assignments.destroy', ['assignment' => $assignment->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar asignacion" data-swal-text="La asignacion dejara de usarse en procesos nuevos, pero el historial seguira disponible." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No hay asignaciones activas registradas.</td></tr>
                    @endforelse
                    @if ($assignments->count() > 0)
                        <tr data-empty-filter style="display:none;">
                            <td colspan="6" class="text-center text-muted">No se encontraron asignaciones con esos filtros.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
