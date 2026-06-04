@extends('layouts.panel')

@section('title', 'Usuarios')

@section('content')
@php($formVisible = $editUser !== null || $errors->any())

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-user-shield"></i>
                <span>Usuarios del sistema</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#userFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editUser ? 'Editar usuario' : 'Nuevo usuario' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="users-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Usuario, nombre o correo" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Perfil</label>
                <select class="form-control" data-filter-name="role">
                    <option value="">Todos</option>
                    @foreach ($roles as $role)
                        <option value="{{ strtolower($role->nombre) }}">{{ ucfirst($role->nombre) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="users-table">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Perfil</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr data-filter-row data-text="{{ strtolower($user->nombre_usuario.' '.$user->nombres.' '.$user->apellidos.' '.$user->email.' '.$user->rol) }}" data-role="{{ strtolower($user->rol) }}">
                        <td>{{ $user->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($user->nombre_usuario, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $user->nombre_usuario }}</strong>
                                    <div class="small text-muted">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->nombres }} {{ $user->apellidos }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge badge-info text-uppercase">{{ $user->rol }}</span></td>
                        <td><span class="maint-status maint-status-active">Activo</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info user-view-button"
                                data-user="{{ $user->nombre_usuario }}"
                                data-name="{{ $user->nombres }} {{ $user->apellidos }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->rol }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ \App\Support\AppUrl::route('users.index') }}?edit_user={{ $user->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('users.destroy', ['user' => $user->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar usuario" data-swal-text="El usuario perdera acceso al sistema, pero su historial se mantendra." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No hay usuarios registrados.</td></tr>
                @endforelse
                @if ($users->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="7" class="text-center text-muted">No se encontraron usuarios con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="userFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editUser ? 'Editar usuario' : 'Nuevo usuario' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editUser ? \App\Support\AppUrl::route('users.update', ['user' => $editUser->id]) : \App\Support\AppUrl::route('users.store') }}">
                    @csrf
                    @if ($editUser) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-2 form-group"><label>Perfil</label><select name="rol_id" class="form-control" required>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected(old('rol_id', $editUser->rol_id ?? '') == $role->id)>{{ ucfirst($role->nombre) }}</option>@endforeach</select></div>
                        <div class="col-md-2 form-group"><label>Usuario</label><input name="nombre_usuario" class="form-control" value="{{ old('nombre_usuario', $editUser->nombre_usuario ?? '') }}" required></div>
                        <div class="col-md-2 form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editUser->nombres ?? '') }}" required></div>
                        <div class="col-md-2 form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editUser->apellidos ?? '') }}" required></div>
                        <div class="col-md-2 form-group"><label>Correo</label><input type="email" name="email" class="form-control" value="{{ old('email', $editUser->email ?? '') }}" required></div>
                        <div class="col-md-2 form-group"><label>{{ $editUser ? 'Nueva contrasena' : 'Contrasena' }}</label><input type="password" name="password" class="form-control" {{ $editUser ? '' : 'required' }}></div>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editUser ? 'Guardar cambios' : 'Agregar usuario' }}</button>
                    @if ($editUser)<a href="{{ \App\Support\AppUrl::route('users.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de usuario</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Usuario:</strong> <span data-user-field="user"></span></li>
                    <li><strong>Nombre:</strong> <span data-user-field="name"></span></li>
                    <li><strong>Correo:</strong> <span data-user-field="email"></span></li>
                    <li><strong>Perfil:</strong> <span data-user-field="role"></span></li>
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
        $('#userFormModal').modal('show');
        @endif

        document.querySelectorAll('.user-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-user-field="user"]').textContent = button.dataset.user;
                document.querySelector('[data-user-field="name"]').textContent = button.dataset.name;
                document.querySelector('[data-user-field="email"]').textContent = button.dataset.email;
                document.querySelector('[data-user-field="role"]').textContent = button.dataset.role;
                $('#userViewModal').modal('show');
            });
        });
    });
</script>
@endpush
