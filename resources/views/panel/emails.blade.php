@extends('layouts.panel')

@section('title', 'Correos')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editEmail ? 'Editar registro' : 'Nuevo registro de correo' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editEmail ? '/pad/correos/'.$editEmail->id : '/pad/correos' }}">
                    @csrf
                    @if ($editEmail) @method('PATCH') @endif
                    <div class="form-group">
                        <label>Plantilla</label>
                        <select name="plantilla_id" class="form-control" required>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}" @selected(old('plantilla_id', $editEmail->plantilla_id ?? '') == $template->id)>{{ $template->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Familiar</label>
                        <select name="padre_id" class="form-control" required>
                            <option value="">Seleccione un familiar</option>
                            @foreach ($familyMembers as $familyMember)
                                <option value="{{ $familyMember->id }}" @selected(old('padre_id', $editEmail->padre_id ?? '') == $familyMember->id)>{{ $familyMember->nombres }} {{ $familyMember->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Alumno</label>
                        <select name="alumno_id" class="form-control" required>
                            <option value="">Seleccione un alumno</option>
                            @foreach ($studentsForEmail as $student)
                                <option value="{{ $student->id }}" @selected(old('alumno_id', $editEmail->alumno_id ?? '') == $student->id)>{{ $student->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trimestre</label>
                        <select name="trimestre_id" class="form-control" required>
                            @foreach ($trimesters as $trimester)
                                <option value="{{ $trimester->id }}" @selected(old('trimestre_id', $editEmail->trimestre_id ?? '') == $trimester->id)>{{ $trimester->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control" required>
                            @foreach (['pendiente', 'enviado', 'fallido'] as $status)
                                <option value="{{ $status }}" @selected(old('estado', $editEmail->estado ?? 'pendiente') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editEmail ? 'Guardar cambios' : 'Agregar registro' }}</button>
                    @if ($editEmail)<a href="/pad/correos" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historial de correos</h3>
                <div class="card-tools w-100 mt-2 mt-md-0">
                    <div class="filter-toolbar justify-content-md-end" data-filter-target="emails-table">
                        <div class="form-group">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Familiar, alumno o plantilla" data-filter-name="text">
                        </div>
                        <div class="form-group">
                            <label class="small text-muted mb-1">Estado</label>
                            <select class="form-control form-control-sm" data-filter-name="status">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="enviado">Enviado</option>
                                <option value="fallido">Fallido</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small text-muted mb-1">Trimestre</label>
                            <select class="form-control form-control-sm" data-filter-name="trimester">
                                <option value="">Todos</option>
                                @foreach ($trimesters as $trimester)
                                    <option value="{{ strtolower($trimester->nombre) }}">{{ $trimester->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover" id="emails-table">
                    <thead class="bg-light"><tr><th>#</th><th>Familiar</th><th>Alumno</th><th>Plantilla</th><th>Trimestre</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($emails as $email)
                        <tr data-filter-row data-text="{{ strtolower($email->nombres.' '.$email->apellidos.' '.$email->email_principal.' '.$email->alumno_nombres.' '.$email->alumno_apellidos.' '.$email->plantilla.' '.($email->parentesco ?? '')) }}" data-status="{{ strtolower($email->estado) }}" data-trimester="{{ strtolower($email->trimestre) }}">
                            <td>{{ $email->id }}</td>
                            <td><strong>{{ $email->nombres }} {{ $email->apellidos }}</strong><br><small>{{ $email->email_principal }}{{ $email->parentesco ? ' | '.$email->parentesco : '' }}</small></td>
                            <td>{{ $email->alumno_nombres }} {{ $email->alumno_apellidos }}</td>
                            <td>{{ $email->plantilla }}</td>
                            <td>{{ $email->trimestre }}</td>
                            <td>
                                @if ($email->estado === 'enviado')
                                    <span class="badge badge-success">Enviado</span>
                                @elseif ($email->estado === 'pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                @else
                                    <span class="badge badge-danger">Fallido</span>
                                @endif
                            </td>
                            <td class="action-cell">
                                <a href="/pad/correos?edit_email={{ $email->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/correos/'.$email->id }}" data-swal-confirm="true" data-swal-title="Desactivar registro de correo" data-swal-text="El historial quedara oculto del mantenimiento, pero no se eliminara fisicamente." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay correos registrados.</td></tr>
                    @endforelse
                    @if ($emails->count() > 0)
                        <tr data-empty-filter style="display:none;">
                            <td colspan="7" class="text-center text-muted">No se encontraron correos con esos filtros.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
