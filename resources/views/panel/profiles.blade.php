@extends('layouts.panel')

@section('title', 'Perfiles')

@section('content')
@php($selectedMenus = old('menu_ids', $editProfile?->menus->pluck('id')->all() ?? []))
@php($formVisible = $editProfile !== null || $errors->any())

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-user-lock"></i>
                <span>Perfiles registrados</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#profileFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editProfile ? 'Editar perfil' : 'Nuevo perfil' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="profiles-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Perfil, descripcion o permiso" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select class="form-control" data-filter-name="status">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="profiles-table">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Perfil</th>
                    <th>Descripcion</th>
                    <th>Usuarios</th>
                    <th>Opciones</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($profiles as $profile)
                    <tr data-filter-row data-text="{{ strtolower($profile->nombre.' '.$profile->descripcion.' '.$profile->permisos) }}" data-status="activo">
                        <td>{{ $profile->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($profile->nombre, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ ucfirst($profile->nombre) }}</strong>
                                    <div class="small text-muted">{{ $profile->total_permisos }} opciones habilitadas</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $profile->descripcion ?: 'Sin descripcion' }}</td>
                        <td>{{ $profile->total_usuarios }}</td>
                        <td><div class="small">{{ $profile->permisos ?: 'Sin accesos asignados' }}</div></td>
                        <td><span class="maint-status maint-status-active">Activo</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info profile-view-button"
                                data-name="{{ ucfirst($profile->nombre) }}"
                                data-description="{{ $profile->descripcion ?: 'Sin descripcion' }}"
                                data-users="{{ $profile->total_usuarios }}"
                                data-options="{{ $profile->total_permisos }}"
                                data-permissions="{{ $profile->permisos ?: 'Sin accesos asignados' }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ \App\Support\AppUrl::route('profiles.index') }}?edit_profile={{ $profile->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('profiles.destroy', ['role' => $profile->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar perfil" data-swal-text="El perfil dejara de estar disponible para nuevos usuarios." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No hay perfiles registrados.</td></tr>
                @endforelse
                @if ($profiles->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="7" class="text-center text-muted">No se encontraron perfiles con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="profileFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editProfile ? 'Editar perfil' : 'Nuevo perfil' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editProfile ? \App\Support\AppUrl::route('profiles.update', ['role' => $editProfile->id]) : \App\Support\AppUrl::route('profiles.store') }}">
                    @csrf
                    @if ($editProfile) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nombre del perfil</label><input name="nombre" class="form-control" value="{{ old('nombre', $editProfile->nombre ?? '') }}" required></div>
                        <div class="col-md-8 form-group"><label>Descripcion</label><textarea name="descripcion" class="form-control" rows="2" placeholder="Describe el alcance del perfil">{{ old('descripcion', $editProfile->descripcion ?? '') }}</textarea></div>
                    </div>
                    <div class="form-group">
                        <label>Opciones habilitadas</label>
                        <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
                            <div class="row">
                                @foreach ($permissionMenus as $menuItem)
                                    <div class="col-md-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input" type="checkbox" id="menu_{{ $menuItem->id }}" name="menu_ids[]" value="{{ $menuItem->id }}" @checked(in_array($menuItem->id, $selectedMenus))>
                                            <label class="custom-control-label" for="menu_{{ $menuItem->id }}"><i class="{{ $menuItem->icono }} mr-1 text-muted"></i>{{ $menuItem->nombre }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editProfile ? 'Guardar cambios' : 'Crear perfil' }}</button>
                    @if ($editProfile)<a href="{{ \App\Support\AppUrl::route('profiles.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profileViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de perfil</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Perfil:</strong> <span data-profile-field="name"></span></li>
                    <li><strong>Descripcion:</strong> <span data-profile-field="description"></span></li>
                    <li><strong>Usuarios:</strong> <span data-profile-field="users"></span></li>
                    <li><strong>Opciones:</strong> <span data-profile-field="options"></span></li>
                    <li><strong>Permisos:</strong> <span data-profile-field="permissions"></span></li>
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
        $('#profileFormModal').modal('show');
        @endif

        document.querySelectorAll('.profile-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-profile-field="name"]').textContent = button.dataset.name;
                document.querySelector('[data-profile-field="description"]').textContent = button.dataset.description;
                document.querySelector('[data-profile-field="users"]').textContent = button.dataset.users;
                document.querySelector('[data-profile-field="options"]').textContent = button.dataset.options;
                document.querySelector('[data-profile-field="permissions"]').textContent = button.dataset.permissions;
                $('#profileViewModal').modal('show');
            });
        });
    });
</script>
@endpush
