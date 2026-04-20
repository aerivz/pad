@extends('layouts.panel')

@section('title', 'Materias')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editSubject ? 'Editar materia' : 'Nueva materia' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editSubject ? '/pad/materias/'.$editSubject->id : '/pad/materias' }}">
                    @csrf
                    @if ($editSubject) @method('PATCH') @endif
                    <div class="form-group"><label>Nombre</label><input name="nombre" class="form-control" value="{{ old('nombre', $editSubject->nombre ?? '') }}" required></div>
                    <button class="btn btn-primary btn-sm">{{ $editSubject ? 'Guardar cambios' : 'Agregar materia' }}</button>
                    @if ($editSubject)<a href="/pad/materias" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Catalogo de materias</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead class="bg-light"><tr><th>#</th><th>Materia</th><th>Profesores</th><th>Secciones</th><th>Promedio</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($subjects as $subject)
                        <tr>
                            <td>{{ $subject->id }}</td>
                            <td><strong>{{ $subject->nombre }}</strong></td>
                            <td>{{ $subject->total_profesores }}</td>
                            <td>{{ $subject->total_secciones }}</td>
                            <td>{{ $subject->promedio ?? '-' }}</td>
                            <td class="action-cell">
                                <a href="/pad/materias?edit_subject={{ $subject->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/materias/'.$subject->id }}" data-swal-confirm="true" data-swal-title="Desactivar materia" data-swal-text="La materia quedara inactiva para nuevos procesos, pero el historial se conservara." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
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
