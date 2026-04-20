@extends('layouts.panel')

@section('title', 'Usuarios')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editUser ? 'Editar usuario' : 'Nuevo usuario' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editUser ? '/pad/usuarios/'.$editUser->id : '/pad/usuarios' }}">
                    @csrf
                    @if ($editUser) @method('PATCH') @endif
                    <div class="form-group"><label>Perfil</label><select name="rol_id" class="form-control" required>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected(old('rol_id', $editUser->rol_id ?? '') == $role->id)>{{ ucfirst($role->nombre) }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Usuario</label><input name="nombre_usuario" class="form-control" value="{{ old('nombre_usuario', $editUser->nombre_usuario ?? '') }}" required></div>
                    <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editUser->nombres ?? '') }}" required></div>
                    <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editUser->apellidos ?? '') }}" required></div>
                    <div class="form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $editUser->email ?? '') }}" required></div>
                    <div class="form-group"><label>{{ $editUser ? 'Nueva contrasena (opcional)' : 'Contrasena' }}</label><input type="password" name="password" class="form-control" {{ $editUser ? '' : 'required' }}></div>
                    <button class="btn btn-primary btn-sm">{{ $editUser ? 'Guardar cambios' : 'Agregar usuario' }}</button>
                    @if ($editUser)<a href="/pad/usuarios" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Usuarios del sistema</h3>
                <div class="card-tools w-100 mt-2 mt-md-0">
                    <div class="filter-toolbar justify-content-md-end" data-filter-target="users-table">
                        <div class="form-group">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Usuario, nombre o correo" data-filter-name="text">
                        </div>
                        <div class="form-group">
                            <label class="small text-muted mb-1">Perfil</label>
                            <select class="form-control form-control-sm" data-filter-name="role">
                                <option value="">Todos</option>
                                @foreach ($roles as $role)
                                    <option value="{{ strtolower($role->nombre) }}">{{ ucfirst($role->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover" id="users-table">
                    <thead class="bg-light"><tr><th>#</th><th>Usuario</th><th>Nombre</th><th>Correo</th><th>Perfil</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr data-filter-row data-text="{{ strtolower($user->nombre_usuario.' '.$user->nombres.' '.$user->apellidos.' '.$user->email.' '.$user->rol) }}" data-role="{{ strtolower($user->rol) }}">
                            <td>{{ $user->id }}</td>
                            <td><strong>{{ $user->nombre_usuario }}</strong></td>
                            <td>{{ $user->nombres }} {{ $user->apellidos }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-info text-uppercase">{{ $user->rol }}</span></td>
                            <td class="action-cell">
                                <a href="/pad/usuarios?edit_user={{ $user->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/usuarios/'.$user->id }}" data-swal-confirm="true" data-swal-title="Desactivar usuario" data-swal-text="El usuario perdera acceso al sistema, pero su historial se mantendra." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                            </td>
                        </tr>
                    @endforeach
                    <tr data-empty-filter style="display:none;">
                        <td colspan="6" class="text-center text-muted">No se encontraron usuarios con esos filtros.</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
