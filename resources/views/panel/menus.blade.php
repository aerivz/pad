@extends('layouts.panel')

@section('title', 'Menus')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editMenu ? 'Editar menu' : 'Nuevo menu' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editMenu ? '/pad/menus/'.$editMenu->id : '/pad/menus' }}">
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

                    <div class="form-group">
                        <label>Clave</label>
                        <input name="clave" class="form-control" value="{{ old('clave', $editMenu->clave ?? '') }}" placeholder="menus" required>
                    </div>

                    <div class="form-group">
                        <label>Nombre</label>
                        <input name="nombre" class="form-control" value="{{ old('nombre', $editMenu->nombre ?? '') }}" placeholder="Menus" required>
                    </div>

                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe para que sirve la opcion">{{ old('descripcion', $editMenu->descripcion ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Icono Font Awesome</label>
                        <input name="icono" class="form-control" value="{{ old('icono', $editMenu->icono ?? '') }}" placeholder="fas fa-bars">
                    </div>

                    <div class="form-group">
                        <label>URL</label>
                        <input name="url" class="form-control" value="{{ old('url', $editMenu->url ?? '') }}" placeholder="/pad/menus" required>
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
                    @if ($editMenu)<a href="/pad/menus" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Menus registrados</h3>
                <div class="card-tools w-100 mt-2 mt-md-0">
                    <div class="filter-toolbar justify-content-md-end" data-filter-target="menus-table">
                        <div class="form-group">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Nombre, clave, tabla o URL" data-filter-name="text">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover" id="menus-table">
                    <thead class="bg-light"><tr><th>#</th><th>Menu</th><th>Ruta</th><th>Tablas asociadas</th><th>Orden</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($menus as $menuItem)
                        <tr data-filter-row data-text="{{ strtolower($menuItem->clave.' '.$menuItem->nombre.' '.$menuItem->descripcion.' '.$menuItem->url.' '.$menuItem->tablas_relacionadas) }}">
                            <td>{{ $menuItem->id }}</td>
                            <td>
                                <strong><i class="{{ $menuItem->icono }} mr-1 text-muted"></i>{{ $menuItem->nombre }}</strong><br>
                                <small class="text-muted">{{ $menuItem->clave }}</small>
                                @if ($menuItem->descripcion)
                                    <div class="small text-muted">{{ $menuItem->descripcion }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $menuItem->url }}
                                @if ($menuItem->parent_id)
                                    <div class="small text-muted">Submenu</div>
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
                            <td class="action-cell">
                                <a href="/pad/menus?edit_menu={{ $menuItem->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/menus/'.$menuItem->id }}" data-swal-confirm="true" data-swal-title="Desactivar menu" data-swal-text="El menu dejara de mostrarse en el sistema, pero no se eliminara fisicamente." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
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
</div>
@endsection
