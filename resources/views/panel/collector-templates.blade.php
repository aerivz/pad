@extends('layouts.panel')

@section('title', 'Plantillas de Notas')

@section('content')
@php
    $templateFormVisible = $editTemplate !== null || old('nombre') !== null || old('categorias.0.nombre') !== null;
    $templateRows = old('categorias', $editTemplate
        ? $editTemplate->items->map(fn ($item) => [
            'nombre' => $item->nombre,
            'porcentaje' => rtrim(rtrim(number_format($item->porcentaje, 2, '.', ''), '0'), '.'),
            'tipo_calculo' => $item->tipo_calculo,
            'orden' => $item->orden,
        ])->values()->all()
        : [
            ['nombre' => 'Tareas', 'porcentaje' => '10', 'tipo_calculo' => 'normal', 'orden' => 1],
            ['nombre' => 'Examenes', 'porcentaje' => '25', 'tipo_calculo' => 'normal', 'orden' => 2],
            ['nombre' => 'Laboratorios', 'porcentaje' => '20', 'tipo_calculo' => 'laboratorio', 'orden' => 3],
            ['nombre' => 'Actividades', 'porcentaje' => '15', 'tipo_calculo' => 'normal', 'orden' => 4],
            ['nombre' => 'Expresion Oral y Escrita', 'porcentaje' => '10', 'tipo_calculo' => 'normal', 'orden' => 5],
            ['nombre' => 'Participacion', 'porcentaje' => '10', 'tipo_calculo' => 'normal', 'orden' => 6],
            ['nombre' => 'Dominio Conceptual y Semantica', 'porcentaje' => '10', 'tipo_calculo' => 'normal', 'orden' => 7],
        ]);
@endphp

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-layer-group"></i>
                <span>Plantillas de colector</span>
            </div>
            <div class="maint-actions">
                <a href="{{ \App\Support\AppUrl::route('subjects.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-book-open mr-1"></i>Materias
                </a>
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#collectorTemplateModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editTemplate ? 'Editar plantilla' : 'Nueva plantilla' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="collector-templates-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Codigo, nombre o descripcion" data-filter-name="text">
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
            <table class="table table-hover maint-table" id="collector-templates-table">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Plantilla</th>
                    <th>Codigo</th>
                    <th>Categorias</th>
                    <th>Total</th>
                    <th>Materias</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($templates as $template)
                    @php
                        $summary = $template->items
                            ->map(fn ($item) => $item->nombre.' '.$item->porcentaje.'%')
                            ->implode(' | ');
                    @endphp
                    <tr data-filter-row data-text="{{ strtolower($template->codigo.' '.$template->nombre.' '.($template->descripcion ?? '').' '.$summary) }}" data-status="activo">
                        <td>{{ $template->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($template->nombre, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $template->nombre }}</strong>
                                    <div class="small text-muted">{{ $template->descripcion ?: 'Sin descripcion' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $template->codigo }}</code></td>
                        <td>
                            <div class="small">{{ $template->total_categorias }} categorias</div>
                            <div class="text-muted small" style="max-width:320px;">{{ $summary }}</div>
                        </td>
                        <td>{{ rtrim(rtrim(number_format($template->porcentaje_total, 2), '0'), '.') }}%</td>
                        <td>{{ $template->total_materias }}</td>
                        <td><span class="maint-status maint-status-active">Activa</span></td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info collector-template-view"
                                data-name="{{ $template->nombre }}"
                                data-code="{{ $template->codigo }}"
                                data-description="{{ $template->descripcion ?: 'Sin descripcion' }}"
                                data-total="{{ rtrim(rtrim(number_format($template->porcentaje_total, 2), '0'), '.') }}%"
                                data-subjects="{{ $template->total_materias }}"
                                data-categories="{{ $summary }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ \App\Support\AppUrl::route('collector-templates.index') }}?edit_template={{ $template->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('collector-templates.destroy', ['template' => $template->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar plantilla" data-swal-text="La plantilla quedara inactiva y ya no podra asignarse a nuevas materias." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">No hay plantillas registradas.</td></tr>
                @endforelse
                @if ($templates->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="8" class="text-center text-muted">No se encontraron plantillas con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="collectorTemplateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editTemplate ? 'Editar plantilla de colector' : 'Nueva plantilla de colector' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editTemplate ? \App\Support\AppUrl::route('collector-templates.update', ['template' => $editTemplate->id]) : \App\Support\AppUrl::route('collector-templates.store') }}">
                    @csrf
                    @if ($editTemplate) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $editTemplate->nombre ?? '') }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Codigo</label>
                            <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $editTemplate->codigo ?? '') }}" placeholder="base_general">
                            <small class="text-muted">Si lo dejas vacio, se genera desde nombre.</small>
                        </div>
                        <div class="col-md-5 form-group">
                            <label>Descripcion</label>
                            <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion', $editTemplate->descripcion ?? '') }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Categorias</label>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-template-category">
                            <i class="fas fa-plus mr-1"></i>Agregar categoria
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover maint-table" id="template-categories-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Categoria</th>
                                    <th>%</th>
                                    <th>Tipo</th>
                                    <th>Orden</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($templateRows as $index => $row)
                                <tr>
                                    <td><input type="text" name="categorias[{{ $index }}][nombre]" class="form-control" value="{{ $row['nombre'] ?? '' }}" required></td>
                                    <td><input type="number" step="0.01" min="0.01" max="100" name="categorias[{{ $index }}][porcentaje]" class="form-control template-percentage" value="{{ $row['porcentaje'] ?? '' }}" required></td>
                                    <td>
                                        <select name="categorias[{{ $index }}][tipo_calculo]" class="form-control">
                                            <option value="normal" @selected(($row['tipo_calculo'] ?? 'normal') === 'normal')>Normal</option>
                                            <option value="laboratorio" @selected(($row['tipo_calculo'] ?? '') === 'laboratorio')>Laboratorio</option>
                                        </select>
                                    </td>
                                    <td><input type="number" min="1" max="999" name="categorias[{{ $index }}][orden]" class="form-control template-order" value="{{ $row['orden'] ?? ($index + 1) }}" required></td>
                                    <td class="text-right"><button type="button" class="btn btn-xs btn-danger remove-template-category"><i class="fas fa-times"></i></button></td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="5" class="text-right">Total configurado: <span id="template-total-percentage">0%</span></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-success btn-sm">{{ $editTemplate ? 'Guardar plantilla' : 'Crear plantilla' }}</button>
                        @if ($editTemplate)<a href="{{ \App\Support\AppUrl::route('collector-templates.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="collectorTemplateViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de plantilla</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Nombre:</strong> <span data-template-field="name"></span></li>
                    <li><strong>Codigo:</strong> <span data-template-field="code"></span></li>
                    <li><strong>Descripcion:</strong> <span data-template-field="description"></span></li>
                    <li><strong>Total:</strong> <span data-template-field="total"></span></li>
                    <li><strong>Materias:</strong> <span data-template-field="subjects"></span></li>
                    <li><strong>Categorias:</strong> <span data-template-field="categories"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if ($templateFormVisible)
        $('#collectorTemplateModal').modal('show');
        @endif

        document.querySelectorAll('.collector-template-view').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-template-field="name"]').textContent = button.dataset.name;
                document.querySelector('[data-template-field="code"]').textContent = button.dataset.code;
                document.querySelector('[data-template-field="description"]').textContent = button.dataset.description;
                document.querySelector('[data-template-field="total"]').textContent = button.dataset.total;
                document.querySelector('[data-template-field="subjects"]').textContent = button.dataset.subjects;
                document.querySelector('[data-template-field="categories"]').textContent = button.dataset.categories;
                $('#collectorTemplateViewModal').modal('show');
            });
        });

        const tableBody = document.querySelector('#template-categories-table tbody');
        const addButton = document.getElementById('add-template-category');
        const totalLabel = document.getElementById('template-total-percentage');

        if (!tableBody || !addButton || !totalLabel) {
            return;
        }

        const bindRowEvents = function (row) {
            row.querySelector('.remove-template-category')?.addEventListener('click', function () {
                if (tableBody.querySelectorAll('tr').length === 1) {
                    return;
                }

                row.remove();
                reindexRows();
                syncTotal();
            });

            row.querySelector('.template-percentage')?.addEventListener('input', syncTotal);
        };

        const reindexRows = function () {
            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row, index) {
                row.querySelectorAll('input, select').forEach(function (field) {
                    field.name = field.name.replace(/categorias\\[\\d+\\]/, 'categorias[' + index + ']');
                });

                const orderInput = row.querySelector('.template-order');

                if (orderInput && !orderInput.value) {
                    orderInput.value = index + 1;
                }
            });
        };

        const syncTotal = function () {
            const total = Array.from(tableBody.querySelectorAll('.template-percentage')).reduce(function (carry, input) {
                const value = parseFloat(input.value || '0');
                return carry + (Number.isFinite(value) ? value : 0);
            }, 0);

            totalLabel.textContent = total.toFixed(2).replace(/\\.00$/, '') + '%';
        };

        addButton.addEventListener('click', function () {
            const index = tableBody.querySelectorAll('tr').length;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="categorias[${index}][nombre]" class="form-control" required></td>
                <td><input type="number" step="0.01" min="0.01" max="100" name="categorias[${index}][porcentaje]" class="form-control template-percentage" required></td>
                <td>
                    <select name="categorias[${index}][tipo_calculo]" class="form-control">
                        <option value="normal">Normal</option>
                        <option value="laboratorio">Laboratorio</option>
                    </select>
                </td>
                <td><input type="number" min="1" max="999" name="categorias[${index}][orden]" class="form-control template-order" value="${index + 1}" required></td>
                <td class="text-right"><button type="button" class="btn btn-xs btn-danger remove-template-category"><i class="fas fa-times"></i></button></td>
            `;
            tableBody.appendChild(row);
            bindRowEvents(row);
            syncTotal();
        });

        Array.from(tableBody.querySelectorAll('tr')).forEach(bindRowEvents);
        syncTotal();
    });
</script>
@endpush
