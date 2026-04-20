@extends('layouts.panel')

@section('title', 'Perfiles')

@section('content')
@php($selectedMenus = old('menu_ids', $editProfile?->menus->pluck('id')->all() ?? []))

<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editProfile ? 'Editar perfil' : 'Nuevo perfil' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editProfile ? '/pad/perfiles/'.$editProfile->id : '/pad/perfiles' }}">
                    @csrf
                    @if ($editProfile) @method('PATCH') @endif

                    <div class="form-group">
                        <label>Nombre del perfil</label>
                        <input name="nombre" class="form-control" value="{{ old('nombre', $editProfile->nombre ?? '') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe el alcance del perfil">{{ old('descripcion', $editProfile->descripcion ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Opciones habilitadas</label>
                        <div class="border rounded p-2" style="max-height: 320px; overflow-y: auto;">
                            @foreach ($permissionMenus as $menuItem)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input class="custom-control-input" type="checkbox" id="menu_{{ $menuItem->id }}" name="menu_ids[]" value="{{ $menuItem->id }}" @checked(in_array($menuItem->id, $selectedMenus))>
                                    <label class="custom-control-label" for="menu_{{ $menuItem->id }}">
                                        <i class="{{ $menuItem->icono }} mr-1 text-muted"></i>{{ $menuItem->nombre }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Selecciona las pantallas a las que este perfil podra ingresar.</small>
                    </div>

                    <button class="btn btn-primary btn-sm">{{ $editProfile ? 'Guardar cambios' : 'Crear perfil' }}</button>
                    @if ($editProfile)<a href="/pad/perfiles" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Perfiles registrados</h3>
                <div class="card-tools w-100 mt-2 mt-md-0">
                    <div class="filter-toolbar justify-content-md-end" data-filter-target="profiles-table">
                        <div class="form-group">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Perfil o descripcion" data-filter-name="text">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover" id="profiles-table">
                    <thead class="bg-light"><tr><th>#</th><th>Perfil</th><th>Descripcion</th><th>Usuarios</th><th>Opciones</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($profiles as $profile)
                        <tr data-filter-row data-text="{{ strtolower($profile->nombre.' '.$profile->descripcion.' '.$profile->permisos) }}">
                            <td>{{ $profile->id }}</td>
                            <td><strong>{{ ucfirst($profile->nombre) }}</strong></td>
                            <td>{{ $profile->descripcion ?: 'Sin descripcion' }}</td>
                            <td><span class="badge badge-info">{{ $profile->total_usuarios }}</span></td>
                            <td>
                                <div class="small">{{ $profile->permisos ?: 'Sin accesos asignados' }}</div>
                                <small class="text-muted">{{ $profile->total_permisos }} opciones</small>
                            </td>
                            <td class="action-cell">
                                <a href="/pad/perfiles?edit_profile={{ $profile->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/perfiles/'.$profile->id }}" data-swal-confirm="true" data-swal-title="Desactivar perfil" data-swal-text="El perfil dejara de estar disponible para nuevos usuarios." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                            </td>
                        </tr>
                    @endforeach
                    <tr data-empty-filter style="display:none;">
                        <td colspan="6" class="text-center text-muted">No se encontraron perfiles con esos filtros.</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
