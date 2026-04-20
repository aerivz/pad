@extends('layouts.panel')

@section('title', 'Profesores')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editTeacher ? 'Editar profesor' : 'Nuevo profesor' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editTeacher ? '/pad/profesores/'.$editTeacher->id : '/pad/profesores' }}">
                    @csrf
                    @if ($editTeacher) @method('PATCH') @endif
                    <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editTeacher->nombres ?? '') }}" required></div>
                    <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editTeacher->apellidos ?? '') }}" required></div>
                    <div class="form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $editTeacher->email ?? '') }}" required></div>
                    <div class="form-group"><label>Especialidad</label><input name="especialidad" class="form-control" value="{{ old('especialidad', $editTeacher->especialidad ?? '') }}"></div>
                    <button class="btn btn-primary btn-sm">{{ $editTeacher ? 'Guardar cambios' : 'Agregar profesor' }}</button>
                    @if ($editTeacher)<a href="/pad/profesores" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row">
            @foreach ($teachers as $teacher)
                <div class="col-md-6 mb-3">
                    <div class="card card-widget widget-user-2 shadow-sm">
                        <div class="widget-user-header bg-info py-3">
                            <div class="widget-user-image"><span class="avatar-initials" style="width:50px;height:50px;font-size:1rem;background:rgba(255,255,255,.3);">{{ strtoupper(substr($teacher->nombres, 0, 1).substr($teacher->apellidos, 0, 1)) }}</span></div>
                            <h5 class="widget-user-username">{{ $teacher->nombres }} {{ $teacher->apellidos }}</h5>
                            <h6 class="widget-user-desc">{{ $teacher->especialidad ?? 'Sin especialidad' }}</h6>
                        </div>
                        <div class="card-footer p-0">
                            <ul class="nav flex-column">
                                <li class="nav-item"><span class="nav-link">Correo <span class="float-right small text-muted">{{ $teacher->email }}</span></span></li>
                                <li class="nav-item"><span class="nav-link">Secciones <span class="float-right badge badge-primary">{{ $teacher->total_secciones }}</span></span></li>
                                <li class="nav-item"><span class="nav-link">Asignaciones <span class="float-right badge badge-secondary">{{ $teacher->total_asignaciones }}</span></span></li>
                                <li class="nav-item"><span class="nav-link small text-muted">{{ $teacher->materias ?: 'Aun sin materias asignadas' }}</span></li>
                                <li class="nav-item text-center py-2">
                                    <a href="/pad/profesores?edit_teacher={{ $teacher->id }}" class="btn btn-xs btn-warning">Editar</a>
                                    <form method="POST" action="{{ '/pad/profesores/'.$teacher->id }}" data-swal-confirm="true" data-swal-title="Desactivar profesor" data-swal-text="El profesor quedara inactivo y sus datos seguiran resguardados en el sistema." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
