@extends('layouts.panel')

@section('title', 'Secciones')

@section('content')
@php($formVisible = $editSection !== null || $errors->any())

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-school"></i>
                <span>Lista de secciones</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#sectionFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editSection ? 'Editar seccion' : 'Nueva seccion' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="sections-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Grado, seccion o año" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Año</label>
                <select class="form-control" data-filter-name="year">
                    <option value="">Todos</option>
                    @foreach ($sections->pluck('anio_escolar')->unique()->sortDesc() as $year)
                        <option value="{{ strtolower((string) $year) }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="maint-tags mb-3">
            @foreach ($sections->pluck('grado')->unique()->take(5) as $grade)
                <span class="maint-tag">{{ $grade }}</span>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="sections-table">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Año</th>
                    <th>Alumnos</th>
                    <th>Materias</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($sections as $section)
                    <tr data-filter-row data-text="{{ strtolower($section->grado.' '.$section->nombre.' '.$section->anio_escolar) }}" data-year="{{ strtolower((string) $section->anio_escolar) }}">
                        <td>{{ $section->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($section->grado, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $section->grado }} {{ $section->nombre }}</strong>
                                    <div class="small text-muted">{{ $section->total_alumnos }} alumnos registrados</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $section->anio_escolar }}</td>
                        <td>{{ $section->total_alumnos }}</td>
                        <td>{{ $section->total_materias }}</td>
                        <td>{{ $section->promedio ?? '-' }}</td>
                        <td><span class="maint-status maint-status-active">Activa</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info section-view-button"
                                data-section="{{ $section->grado }} {{ $section->nombre }}"
                                data-year="{{ $section->anio_escolar }}"
                                data-students="{{ $section->total_alumnos }}"
                                data-subjects="{{ $section->total_materias }}"
                                data-average="{{ $section->promedio ?? 'Sin promedio' }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ \App\Support\AppUrl::route('sections.index') }}?edit_section={{ $section->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('sections.destroy', ['section' => $section->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar seccion" data-swal-text="La seccion quedara inactiva y ya no aparecera en los listados principales." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No hay secciones registradas.</td></tr>
                @endforelse
                @if ($sections->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="8" class="text-center text-muted">No se encontraron secciones con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="sectionFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editSection ? 'Editar seccion' : 'Nueva seccion' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editSection ? \App\Support\AppUrl::route('sections.update', ['section' => $editSection->id]) : \App\Support\AppUrl::route('sections.store') }}">
                    @csrf
                    @if ($editSection) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nombre</label><input name="nombre" class="form-control" value="{{ old('nombre', $editSection->nombre ?? '') }}" required></div>
                        <div class="col-md-4 form-group"><label>Grado</label><input name="grado" class="form-control" value="{{ old('grado', $editSection->grado ?? '') }}" required></div>
                        <div class="col-md-4 form-group"><label>Anio escolar</label><input type="number" name="anio_escolar" class="form-control" value="{{ old('anio_escolar', $editSection->anio_escolar ?? date('Y')) }}" required></div>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editSection ? 'Guardar cambios' : 'Agregar seccion' }}</button>
                    @if ($editSection)<a href="{{ \App\Support\AppUrl::route('sections.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sectionViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de seccion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Seccion:</strong> <span data-section-field="section"></span></li>
                    <li><strong>Año:</strong> <span data-section-field="year"></span></li>
                    <li><strong>Alumnos:</strong> <span data-section-field="students"></span></li>
                    <li><strong>Materias:</strong> <span data-section-field="subjects"></span></li>
                    <li><strong>Promedio:</strong> <span data-section-field="average"></span></li>
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
        $('#sectionFormModal').modal('show');
        @endif

        document.querySelectorAll('.section-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-section-field="section"]').textContent = button.dataset.section;
                document.querySelector('[data-section-field="year"]').textContent = button.dataset.year;
                document.querySelector('[data-section-field="students"]').textContent = button.dataset.students;
                document.querySelector('[data-section-field="subjects"]').textContent = button.dataset.subjects;
                document.querySelector('[data-section-field="average"]').textContent = button.dataset.average;
                $('#sectionViewModal').modal('show');
            });
        });
    });
</script>
@endpush
