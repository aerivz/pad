@extends('layouts.panel')

@section('title', 'Materias')

@section('content')
@php($formVisible = $editSubject !== null || $errors->any())

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-book-open"></i>
                <span>Catalogo de materias</span>
            </div>
            <div class="maint-actions">
                <a href="{{ \App\Support\AppUrl::route('collector-templates.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-layer-group mr-1"></i>Plantillas de notas
                </a>
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#subjectFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editSubject ? 'Editar materia' : 'Nueva materia' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="subjects-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Nombre de la materia" data-filter-name="text">
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
            <table class="table table-hover maint-table" id="subjects-table">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Materia</th>
                    <th>Plantilla</th>
                    <th>Profesores</th>
                    <th>Secciones</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($subjects as $subject)
                    <tr data-filter-row data-text="{{ strtolower($subject->nombre) }}" data-status="activo">
                        <td>{{ $subject->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($subject->nombre, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $subject->nombre }}</strong>
                                    <div class="small text-muted">{{ $subject->total_profesores }} profesores vinculados</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $categoryTemplates->get($subject->plantilla_colector)['name'] ?? 'Sin plantilla' }}</td>
                        <td>{{ $subject->total_profesores }}</td>
                        <td>{{ $subject->total_secciones }}</td>
                        <td>{{ $subject->promedio ?? '-' }}</td>
                        <td><span class="maint-status maint-status-active">Activa</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info subject-view-button"
                                data-name="{{ $subject->nombre }}"
                                data-template="{{ $categoryTemplates->get($subject->plantilla_colector)['name'] ?? 'Sin plantilla' }}"
                                data-teachers="{{ $subject->total_profesores }}"
                                data-sections="{{ $subject->total_secciones }}"
                                data-average="{{ $subject->promedio ?? 'Sin promedio' }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ \App\Support\AppUrl::route('subjects.index') }}?edit_subject={{ $subject->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('subjects.destroy', ['subject' => $subject->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar materia" data-swal-text="La materia quedara inactiva para nuevos procesos, pero el historial se conservara." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No hay materias registradas.</td></tr>
                @endforelse
                @if ($subjects->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="8" class="text-center text-muted">No se encontraron materias con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="subjectFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editSubject ? 'Editar materia' : 'Nueva materia' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editSubject ? \App\Support\AppUrl::route('subjects.update', ['subject' => $editSubject->id]) : \App\Support\AppUrl::route('subjects.store') }}">
                    @csrf
                    @if ($editSubject) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Nombre</label><input name="nombre" class="form-control" value="{{ old('nombre', $editSubject->nombre ?? '') }}" required></div>
                        <div class="col-md-6 form-group">
                            <label>Plantilla del colector</label>
                            <select name="plantilla_colector" class="form-control">
                                <option value="">Sin plantilla</option>
                                @foreach ($categoryTemplates as $templateKey => $template)
                                    <option value="{{ $templateKey }}" @selected(old('plantilla_colector', $editSubject->plantilla_colector ?? $categoryTemplates->keys()->first()) === $templateKey)>{{ $template['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editSubject ? 'Guardar cambios' : 'Agregar materia' }}</button>
                    @if ($editSubject)<a href="{{ \App\Support\AppUrl::route('subjects.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="subjectViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de materia</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Materia:</strong> <span data-subject-field="name"></span></li>
                    <li><strong>Plantilla:</strong> <span data-subject-field="template"></span></li>
                    <li><strong>Profesores:</strong> <span data-subject-field="teachers"></span></li>
                    <li><strong>Secciones:</strong> <span data-subject-field="sections"></span></li>
                    <li><strong>Promedio:</strong> <span data-subject-field="average"></span></li>
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
        $('#subjectFormModal').modal('show');
        @endif

        document.querySelectorAll('.subject-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-subject-field="name"]').textContent = button.dataset.name;
                document.querySelector('[data-subject-field="template"]').textContent = button.dataset.template;
                document.querySelector('[data-subject-field="teachers"]').textContent = button.dataset.teachers;
                document.querySelector('[data-subject-field="sections"]').textContent = button.dataset.sections;
                document.querySelector('[data-subject-field="average"]').textContent = button.dataset.average;
                $('#subjectViewModal').modal('show');
            });
        });
    });
</script>
@endpush
