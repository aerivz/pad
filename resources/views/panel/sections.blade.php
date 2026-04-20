@extends('layouts.panel')

@section('title', 'Secciones')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editSection ? 'Editar seccion' : 'Nueva seccion' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editSection ? '/pad/secciones/'.$editSection->id : '/pad/secciones' }}">
                    @csrf
                    @if ($editSection) @method('PATCH') @endif
                    <div class="form-group"><label>Nombre</label><input name="nombre" class="form-control" value="{{ old('nombre', $editSection->nombre ?? '') }}" required></div>
                    <div class="form-group"><label>Grado</label><input name="grado" class="form-control" value="{{ old('grado', $editSection->grado ?? '') }}" required></div>
                    <div class="form-group"><label>Anio escolar</label><input type="number" name="anio_escolar" class="form-control" value="{{ old('anio_escolar', $editSection->anio_escolar ?? date('Y')) }}" required></div>
                    <button class="btn btn-primary btn-sm">{{ $editSection ? 'Guardar cambios' : 'Agregar seccion' }}</button>
                    @if ($editSection)<a href="/pad/secciones" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Secciones registradas</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead class="bg-light"><tr><th>#</th><th>Seccion</th><th>Anio</th><th>Alumnos</th><th>Materias</th><th>Promedio</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($sections as $section)
                        <tr>
                            <td>{{ $section->id }}</td>
                            <td><strong>{{ $section->grado }} {{ $section->nombre }}</strong></td>
                            <td>{{ $section->anio_escolar }}</td>
                            <td>{{ $section->total_alumnos }}</td>
                            <td>{{ $section->total_materias }}</td>
                            <td>{{ $section->promedio ?? '-' }}</td>
                            <td class="action-cell">
                                <a href="/pad/secciones?edit_section={{ $section->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/secciones/'.$section->id }}" data-swal-confirm="true" data-swal-title="Desactivar seccion" data-swal-text="La seccion quedara inactiva y ya no aparecera en los listados principales." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
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
