@extends('layouts.panel')

@section('title', 'Alumnos')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editStudent ? 'Editar alumno' : 'Nuevo alumno' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editStudent ? '/pad/alumnos/'.$editStudent->id : '/pad/alumnos' }}">
                    @csrf
                    @if ($editStudent) @method('PATCH') @endif
                    <div class="form-group"><label>Seccion</label><select name="seccion_id" class="form-control" required>@foreach ($sections as $section)<option value="{{ $section->id }}" @selected(old('seccion_id', $editStudent->seccion_id ?? '') == $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editStudent->nombres ?? '') }}" required></div>
                    <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editStudent->apellidos ?? '') }}" required></div>
                    <button class="btn btn-primary btn-sm">{{ $editStudent ? 'Guardar cambios' : 'Agregar alumno' }}</button>
                    @if ($editStudent)<a href="/pad/alumnos" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Lista de alumnos</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead class="bg-light"><tr><th>#</th><th>Estudiante</th><th>Seccion</th><th>Familiares</th><th>Promedio</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->id }}</td>
                            <td><strong>{{ $student->nombres }} {{ $student->apellidos }}</strong></td>
                            <td><span class="badge badge-info">{{ $student->grado }} {{ $student->seccion_nombre }}</span></td>
                            <td>{{ $student->total_padres }}</td>
                            <td>{{ $student->promedio ?? '-' }}</td>
                            <td class="action-cell">
                                <a href="/pad/alumnos?edit_student={{ $student->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/alumnos/'.$student->id }}" data-swal-confirm="true" data-swal-title="Desactivar alumno" data-swal-text="El alumno quedara inactivo y no se eliminara fisicamente de la base de datos." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
