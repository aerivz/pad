@extends('layouts.panel')

@section('title', 'Menus')

@section('content')
@php($formVisible = $editMenu !== null || old('clave') !== null || old('nombre') !== null || old('url') !== null)

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-bars"></i>
                <span>Menus registrados</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#menuFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editMenu ? 'Editar menu' : 'Nuevo menu' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="menus-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Nombre, clave, tabla o URL" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select class="form-control" data-filter-name="type">
                    <option value="">Todos</option>
                    <option value="padre">Padre</option>
                    <option value="submenu">Submenu</option>
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="menus-table">
                <thead class="bg-light"><tr><th>#</th><th>Menu</th><th>Ruta</th><th>Tablas asociadas</th><th>Orden</th><th>Acciones</th></tr></thead>
                <tbody>
                @forelse ($menus as $menuItem)
                    <tr data-filter-row data-text="{{ strtolower($menuItem->clave.' '.$menuItem->nombre.' '.$menuItem->descripcion.' '.$menuItem->url.' '.$menuItem->tablas_relacionadas) }}" data-type="{{ $menuItem->parent_id ? 'submenu' : 'padre' }}">
                        <td>{{ $menuItem->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar"><i class="{{ $menuItem->icono ?: 'fas fa-bars' }}"></i></span>
                                <div>
                                    <strong>{{ $menuItem->nombre }}</strong>
                                    <div class="small text-muted">{{ $menuItem->clave }}</div>
                                    @if ($menuItem->descripcion)
                                        <div class="small text-muted">{{ $menuItem->descripcion }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            {{ $menuItem->url }}
                            @if ($menuItem->parent_id)
                                <div class="small text-muted">Submenu</div>
                            @else
                                <div class="small text-muted">Menu padre</div>
                            @endif
                        </td>
                        <td>
                            @foreach (collect(explode(',', $menuItem->tablas_relacionadas ?? ''))->map(fn ($item) => trim($item))->filter() as $tableName)
                                <span class="weight-pill">{{ $tableName }}</span>
                            @endforeach
                            @if (! $menuItem->tablas_relacionadas)
                                <span class="text-muted small">Sin asociacion</span>
                            @endif
                        </td>
                        <td><span class="badge badge-info">{{ $menuItem->orden }}</span></td>
                        <td class="maint-actions-cell">
                            <a href="{{ \App\Support\AppUrl::route('menus.index') }}?edit_menu={{ $menuItem->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('menus.destroy', ['menu' => $menuItem->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar menu" data-swal-text="El menu dejara de mostrarse en el sistema, pero no se eliminara fisicamente." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No hay menus registrados.</td></tr>
                @endforelse
                @if ($menus->count() > 0)
                    <tr data-empty-filter style="display:none;">
                        <td colspan="6" class="text-center text-muted">No se encontraron menus con esos filtros.</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="menuFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editMenu ? 'Editar menu' : 'Nuevo menu' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editMenu ? \App\Support\AppUrl::route('menus.update', ['menu' => $editMenu->id]) : \App\Support\AppUrl::route('menus.store') }}">
                    @csrf
                    @if ($editMenu) @method('PATCH') @endif

                    <div class="form-group">
                        <label>Menu padre</label>
                        <select name="parent_id" class="form-control">
                            <option value="">Sin padre</option>
                            @foreach ($menus->where('id', '!=', $editMenu->id ?? 0) as $parentMenu)
                                <option value="{{ $parentMenu->id }}" @selected((string) old('parent_id', $editMenu->parent_id ?? '') === (string) $parentMenu->id)>{{ $parentMenu->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Clave</label>
                            <input name="clave" class="form-control" value="{{ old('clave', $editMenu->clave ?? '') }}" placeholder="menus" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Nombre</label>
                            <input name="nombre" class="form-control" value="{{ old('nombre', $editMenu->nombre ?? '') }}" placeholder="Menus" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe para que sirve la opcion">{{ old('descripcion', $editMenu->descripcion ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Icono Font Awesome</label>
                            <input name="icono" class="form-control" value="{{ old('icono', $editMenu->icono ?? '') }}" placeholder="fas fa-bars">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>URL</label>
                            <input name="url" class="form-control" value="{{ old('url', $editMenu->url ?? '') }}" placeholder="/menus" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tablas relacionadas</label>
                        <textarea name="tablas_relacionadas" class="form-control" rows="3" placeholder="menus, rol_menu">{{ old('tablas_relacionadas', $editMenu->tablas_relacionadas ?? '') }}</textarea>
                        <small class="text-muted">Escribe nombres de tablas separados por coma.</small>
                    </div>

                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" min="1" name="orden" class="form-control" value="{{ old('orden', $editMenu->orden ?? ($menus->max('orden') + 1)) }}" required>
                    </div>

                    <button class="btn btn-primary btn-sm">{{ $editMenu ? 'Guardar cambios' : 'Crear menu' }}</button>
                    @if ($editMenu)<a href="{{ \App\Support\AppUrl::route('menus.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if ($formVisible)
        $('#menuFormModal').modal('show');
        @endif
    });
</script>
@endpush
