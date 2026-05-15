@extends('layouts.panel')

@section('title', 'Colector de Notas')

@push('styles')
<style>
    .collector-toolbar-card .card-body { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .collector-toolbar-meta { color: #6c757d; font-size: .9rem; }
    .collector-table-card .card-body { padding: 0; }
    .collector-grid { display: flex; align-items: stretch; width: 100%; }
    .collector-grid-fixed { flex: 0 0 380px; max-width: 380px; border-right: 1px solid #dee2e6; background: #fff; }
    .collector-grid-scroll { flex: 1 1 auto; min-width: 0; overflow-x: auto; background: #fff; }
    .collector-grid-fixed .table-responsive,
    .collector-grid-scroll .table-responsive { overflow: visible; }
    .collector-table-card .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
    .collector-table-card th,
    .collector-table-card td { white-space: nowrap; vertical-align: middle; }
    .collector-table-card .grade-input { width: 82px; }
    .collector-modal-table td,
    .collector-modal-table th { vertical-align: middle; }
    .collector-fixed-number { width: 72px; min-width: 72px; max-width: 72px; }
    .collector-fixed-student { width: 308px; min-width: 308px; max-width: 308px; overflow: hidden; text-overflow: ellipsis; }
    .collector-grid-fixed thead th,
    .collector-grid-scroll thead th { background: #f8f9fa; }
    .collector-grid-fixed tbody td,
    .collector-grid-scroll tbody td { height: 70px; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="/pad/notas" class="row">
            <div class="col-md-3">
                <label>Año lectivo</label>
                <select name="anio_escolar" class="form-control" onchange="this.form.submit()">
                    @foreach ($academicYears as $year)
                        <option value="{{ $year }}" @selected((int) $selectedYear === (int) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Seccion</label>
                <select name="seccion_id" class="form-control" onchange="this.form.submit()">
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected((int) $selectedSectionId === (int) $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Materia</label>
                <select name="materia_id" class="form-control" onchange="this.form.submit()">
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((int) $selectedSubjectId === (int) $subject->id)>{{ $subject->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Trimestre</label>
                <select name="trimestre_id" class="form-control">
                    @foreach ($trimesters as $trimester)
                        <option value="{{ $trimester->id }}" @selected((int) $selectedTrimesterId === (int) $trimester->id)>{{ $trimester->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-outline-primary btn-block">Ver</button>
            </div>
        </form>
    </div>
</div>

@if (! $selectedAssignment)
    <div class="alert alert-warning">No existe asignacion activa para el año, seccion y materia seleccionados.</div>
@else
    @if ($gradeBoard['percentage_total'] < 100)
        <div class="alert alert-warning">
            Configuracion incompleta. Categorias suman <strong>{{ rtrim(rtrim(number_format($gradeBoard['percentage_total'], 2), '0'), '.') }}%</strong>.
            No se calculara Report Card hasta llegar a 100%.
        </div>
    @endif

    <div class="card collector-toolbar-card">
        <div class="card-body">
            <div class="collector-toolbar-meta">
                {{ $selectedAssignment->materia }} | {{ $selectedAssignment->grado }} {{ $selectedAssignment->seccion }} | {{ $selectedAssignment->profesor }} | Año {{ $selectedAssignment->anio_escolar }}
            </div>
            <div class="action-toolbar">
                <a href="/pad/notas/plantillas/colector" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv mr-1"></i>Descargar plantilla</a>
                <a href="/pad/notas/plantillas/normalizado" class="btn btn-outline-info btn-sm"><i class="fas fa-file-download mr-1"></i>Descargar activos</a>
                <button type="button" class="btn btn-outline-warning btn-sm" data-toggle="modal" data-target="#categoriesModal"><i class="fas fa-sync-alt mr-1"></i>Categorias configuradas</button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#importModal"><i class="fas fa-file-import mr-1"></i>Importacion masiva</button>
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#categoryModal"><i class="fas fa-plus mr-1"></i>{{ $editCategory ? 'Editar categoria' : 'Nueva categoria' }}</button>
            </div>
        </div>
    </div>

    <div class="card collector-table-card">
        <div class="card-header">
            <h3 class="card-title">Colector de notas</h3>
            <div class="card-tools text-muted small">Total configurado: {{ rtrim(rtrim(number_format($gradeBoard['percentage_total'], 2), '0'), '.') }}% | {{ $gradeBoard['can_calculate_report'] ? 'Report Card activo' : 'Falta completar 100%' }}</div>
        </div>
        <div class="card-body">
            @if ($categories->count() === 0)
                <div class="alert alert-info m-3">Crea categorias primero. Luego podras capturar notas y conducta por alumno.</div>
            @else
                <form method="POST" action="/pad/notas/calificaciones">
                    @csrf
                    <input type="hidden" name="asignacion_id" value="{{ $selectedAssignmentId }}">
                    <input type="hidden" name="trimestre_id" value="{{ $selectedTrimesterId }}">

                    <div class="collector-grid">
                        <div class="collector-grid-fixed">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="collector-fixed-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th rowspan="2" class="collector-fixed-number">#</th>
                                            <th rowspan="2" class="collector-fixed-student">Alumno</th>
                                        </tr>
                                        <tr></tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($gradeBoard['rows'] as $row)
                                        <tr>
                                            <td class="collector-fixed-number">{{ $row['id'] }}</td>
                                            <td class="collector-fixed-student">{{ $row['nombre'] }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="collector-grid-scroll">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="collector-scroll-table">
                                    <thead class="bg-light">
                                        <tr>
                                            @foreach ($gradeBoard['categories'] as $category)
                                                <th colspan="{{ $category->tipo_calculo === 'laboratorio' ? 4 : 6 }}" class="text-center">
                                                    {{ $category->nombre }}<br>
                                                    <small>{{ rtrim(rtrim(number_format($category->porcentaje, 2), '0'), '.') }}% | {{ $category->tipo_calculo }}</small>
                                                </th>
                                            @endforeach
                                            <th rowspan="2" class="text-center">Progress 1</th>
                                            <th rowspan="2" class="text-center">Progress 2</th>
                                            <th rowspan="2" class="text-center">Report Card</th>
                                            <th rowspan="2" class="text-center">Conducta</th>
                                        </tr>
                                        <tr>
                                            @foreach ($gradeBoard['categories'] as $category)
                                                <th class="text-center">1</th>
                                                @if ($category->tipo_calculo === 'normal')
                                                    <th class="text-center">2</th>
                                                @endif
                                                <th class="text-center">PR1</th>
                                                @if ($category->tipo_calculo === 'normal')
                                                    <th class="text-center">3</th>
                                                    <th class="text-center">4</th>
                                                @else
                                                    <th class="text-center">2</th>
                                                @endif
                                                <th class="text-center">PR2</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($gradeBoard['rows'] as $row)
                                        <tr>
                                            @foreach ($gradeBoard['categories'] as $category)
                                                @php($categoryScore = $row['categories'][$category->id] ?? [])
                                                <td class="text-center">
                                                    <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_1]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_1', $categoryScore['nota_1'] ?? '') }}">
                                                </td>
                                                @if ($category->tipo_calculo === 'normal')
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_2]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_2', $categoryScore['nota_2'] ?? '') }}">
                                                    </td>
                                                @endif
                                                <td class="final-cell">{{ $categoryScore['promedio_1'] ?? '—' }}</td>
                                                @if ($category->tipo_calculo === 'normal')
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_3]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_3', $categoryScore['nota_3'] ?? '') }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_4]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_4', $categoryScore['nota_4'] ?? '') }}">
                                                    </td>
                                                @else
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_2]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_2', $categoryScore['nota_2'] ?? '') }}">
                                                    </td>
                                                @endif
                                                <td class="final-cell">{{ $categoryScore['promedio_2'] ?? '—' }}</td>
                                            @endforeach
                                            <td class="final-cell">{{ $row['progress_1'] }}</td>
                                            <td class="final-cell">{{ $row['progress_2'] }}</td>
                                            <td class="final-cell">{{ $row['report_card'] ?? '—' }}</td>
                                            <td class="text-center">
                                                <input type="number" step="0.01" min="0" max="100" name="conduct[{{ $row['id'] }}]" class="form-control form-control-sm grade-input" value="{{ old('conduct.'.$row['id'], $row['conducta'] ?? '') }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border-top">
                        <button class="btn btn-success btn-sm">Guardar colector</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif

<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editCategory ? 'Editar categoria' : 'Nueva categoria' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editCategory ? '/pad/notas/categorias/'.$editCategory->id : '/pad/notas/categorias' }}">
                    @csrf
                    @if ($editCategory) @method('PATCH') @endif
                    <input type="hidden" name="asignacion_id" value="{{ old('asignacion_id', $selectedAssignmentId) }}">
                    <input type="hidden" name="trimestre_id" value="{{ old('trimestre_id', $selectedTrimesterId) }}">

                    <div class="form-group">
                        <label>Categoria</label>
                        <input name="nombre" class="form-control" value="{{ old('nombre', $editCategory->nombre ?? '') }}" placeholder="Ej: Tareas, Examenes, Laboratorios" required>
                    </div>
                    <div class="form-group">
                        <label>Porcentaje (%)</label>
                        <input type="number" step="0.01" min="0.01" max="100" name="porcentaje" class="form-control" value="{{ old('porcentaje', $editCategory->porcentaje ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo de calculo</label>
                        <select name="tipo_calculo" class="form-control" required>
                            <option value="normal" @selected(old('tipo_calculo', $editCategory->tipo_calculo ?? 'normal') === 'normal')>Normal (4 notas)</option>
                            <option value="laboratorio" @selected(old('tipo_calculo', $editCategory->tipo_calculo ?? '') === 'laboratorio')>Laboratorio (2 notas)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" min="1" max="999" name="orden" class="form-control" value="{{ old('orden', $editCategory->orden ?? (count($categories) + 1)) }}" required>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-success btn-sm">{{ $editCategory ? 'Guardar cambios' : 'Agregar categoria' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importacion masiva</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/pad/notas/importar" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="asignacion_id" value="{{ $selectedAssignmentId }}">
                    <input type="hidden" name="trimestre_id" value="{{ $selectedTrimesterId }}">

                    <div class="form-group">
                        <label>Archivo XLSX o CSV</label>
                        <input type="file" name="archivo" class="form-control-file" accept=".xlsx,.csv,.txt" required>
                        <small class="text-muted">Formato colector tipo Excel. Si viene PR1, PR2 y Report Card, sistema recalcula.</small>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="limpiar_antes_importar" name="limpiar_antes_importar" value="1">
                        <label class="form-check-label" for="limpiar_antes_importar">Desactivar categorias, notas y conducta previas antes de importar</label>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary btn-sm">Importar archivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="categoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Categorias configuradas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive collector-modal-table">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light"><tr><th>Categoria</th><th>%</th><th>Tipo</th><th>Notas</th><th></th></tr></thead>
                        <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $category->nombre }}</td>
                                <td>{{ rtrim(rtrim(number_format($category->porcentaje, 2), '0'), '.') }}%</td>
                                <td class="text-capitalize">{{ $category->tipo_calculo }}</td>
                                <td>{{ $category->cantidad_notas }}</td>
                                <td class="action-cell text-right">
                                    <a href="/pad/notas?anio_escolar={{ $selectedYear }}&seccion_id={{ $selectedSectionId }}&materia_id={{ $selectedSubjectId }}&trimestre_id={{ $selectedTrimesterId }}&edit_category={{ $category->id }}" class="btn btn-xs btn-warning">Editar</a>
                                    <form method="POST" action="/pad/notas/categorias/{{ $category->id }}" data-swal-confirm="true" data-swal-title="Desactivar categoria" data-swal-text="Se ocultara categoria y sus notas." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No hay categorias registradas.</td></tr>
                        @endforelse
                        </tbody>
                        @if ($categories->count() > 0)
                            <tfoot class="bg-light">
                                <tr>
                                    <th>Total</th>
                                    <th>{{ rtrim(rtrim(number_format($gradeBoard['percentage_total'], 2), '0'), '.') }}%</th>
                                    <th colspan="3">{{ $gradeBoard['can_calculate_report'] ? 'Listo para Report Card' : 'Falta completar 100%' }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const syncCollectorHeights = function () {
            const fixedTable = document.getElementById('collector-fixed-table');
            const scrollTable = document.getElementById('collector-scroll-table');

            if (!fixedTable || !scrollTable) {
                return;
            }

            const fixedRows = Array.from(fixedTable.querySelectorAll('tr'));
            const scrollRows = Array.from(scrollTable.querySelectorAll('tr'));
            const totalRows = Math.min(fixedRows.length, scrollRows.length);

            fixedRows.forEach(function (row) {
                row.style.height = '';
            });

            scrollRows.forEach(function (row) {
                row.style.height = '';
            });

            for (let index = 0; index < totalRows; index += 1) {
                const fixedHeight = fixedRows[index].getBoundingClientRect().height;
                const scrollHeight = scrollRows[index].getBoundingClientRect().height;
                const targetHeight = Math.max(fixedHeight, scrollHeight);

                fixedRows[index].style.height = targetHeight + 'px';
                scrollRows[index].style.height = targetHeight + 'px';
            }
        };

        syncCollectorHeights();
        window.addEventListener('resize', syncCollectorHeights);

        @if ($editCategory)
            $('#categoryModal').modal('show');
        @endif
    });
</script>
@endpush
