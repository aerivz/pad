@extends('layouts.panel')

@section('title', 'Colector de Notas')

@push('styles')
<style>
    .collector-toolbar-card .card-body { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .collector-toolbar-meta { color: #6c757d; font-size: .9rem; }
    .collector-table-card .card-body { padding: 0; }
    .collector-grid { width: 100%; overflow-x: auto; background: #fff; -webkit-overflow-scrolling: touch; }
    .collector-grid .table { min-width: max-content; }
    .collector-table-card .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
    .collector-table-card th,
    .collector-table-card td { white-space: nowrap; vertical-align: middle; }
    .collector-table-card .grade-input { width: 82px; }
    .collector-modal-table td,
    .collector-modal-table th { vertical-align: middle; }
    .collector-sticky-number { position: sticky; left: 0; z-index: 4; width: 58px; min-width: 58px; max-width: 58px; }
    .collector-sticky-student { position: sticky; left: 58px; z-index: 3; width: 240px; min-width: 240px; max-width: 240px; overflow: hidden; text-overflow: ellipsis; box-shadow: 4px 0 8px rgba(15, 23, 42, .08); }
    .collector-table-card thead .collector-sticky-number,
    .collector-table-card thead .collector-sticky-student { z-index: 6; background: #f8f9fa; }
    .collector-table-card tbody .collector-sticky-number,
    .collector-table-card tbody .collector-sticky-student { background: #fff; }
    .collector-table-card tbody td { height: 70px; }
    @media (max-width: 767.98px) {
        .collector-toolbar-card .card-body { align-items: stretch; }
        .collector-toolbar-meta { width: 100%; }
        .collector-toolbar-card .action-toolbar { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; width: 100%; }
        .collector-toolbar-card .action-toolbar .btn { margin: 0; white-space: normal; }
        .collector-sticky-number { width: 42px; min-width: 42px; max-width: 42px; }
        .collector-sticky-student { left: 42px; width: 150px; min-width: 150px; max-width: 150px; }
        .collector-table-card th, .collector-table-card td { font-size: .78rem; }
        .collector-table-card .grade-input { width: 66px; }
    }
    .collector-readonly .grade-input[disabled] { background: #f8fafc; color: #475569; opacity: 1; }
</style>
@endpush

@section('content')
@php
    $selectedTemplateKey = old('template_key', $selectedAssignment->plantilla_colector ?? $categoryTemplates->keys()->first());
    $selectedTemplate = $selectedTemplateKey ? $categoryTemplates->get($selectedTemplateKey) : null;
    $readOnlyGradeBook = ! $canEditGradeBook;
@endphp
<div class="card">
    <div class="card-body">
        <form method="GET" action="/pad/notas" class="row">
            <div class="col-md-3">
                <label>Anio lectivo</label>
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
                <label>Periodo</label>
                <select name="periodo" class="form-control">
                    @foreach ($periodOptions as $option)
                        <option value="{{ $option['value'] }}" @selected($selectedPeriod === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-outline-primary btn-block">Ver</button>
            </div>
        </form>

        @if ($selectedAssignment && $canViewGradeBook)
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between px-3 py-2 rounded border bg-light" style="gap: .75rem;">
                        <div class="text-muted small">
                            Resumen anual de la materia con ponderacion 20/20/20/20/10/10.
                        </div>
                        @if ($periodExamMeta)
                            <div class="small">
                                <strong>{{ $periodExamMeta['label'] }}</strong> se captura en este trimestre y vale <strong>{{ $periodExamMeta['weight'] }}%</strong>.
                            </div>
                        @else
                            <div class="small">
                                <strong>{{ $selectedPeriodLabel }}</strong> seleccionado para captura del colector.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if (! $selectedAssignment || ! $canViewGradeBook)
    <div class="alert alert-warning">No existe asignacion activa para el anio, seccion y materia seleccionados.</div>
@else
    @if ($readOnlyGradeBook)
        <div class="alert alert-info">
            Vista en modo consulta. Como profesor titular puedes revisar notas de toda tu seccion, pero solo podras modificarlas si esta materia esta asignada a tu usuario.
        </div>
    @endif

    @if ($selectedPeriodType === 'trimester' && $gradeBoard['percentage_total'] < 100)
        <div class="alert alert-warning">
            Configuracion incompleta. Categorias suman <strong>{{ rtrim(rtrim(number_format($gradeBoard['percentage_total'], 2), '0'), '.') }}%</strong>.
            No se calculara Report Card hasta llegar a 100%.
        </div>
    @endif

    <div class="card collector-toolbar-card">
        <div class="card-body">
            <div class="collector-toolbar-meta">
                {{ $selectedAssignment->materia }} | {{ $selectedAssignment->grado }} {{ $selectedAssignment->seccion }} | {{ $selectedAssignment->profesor }} | Anio {{ $selectedAssignment->anio_escolar }}
            </div>
            <div class="action-toolbar">
                <a href="/pad/notas/plantillas/colector" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv mr-1"></i>Descargar plantilla</a>
                <a href="/pad/notas/plantillas/normalizado" class="btn btn-outline-info btn-sm"><i class="fas fa-file-download mr-1"></i>Descargar activos</a>
                <a href="{{ \App\Support\AppUrl::route('collector-templates.index') }}" class="btn btn-outline-dark btn-sm"><i class="fas fa-cogs mr-1"></i>Plantillas de notas</a>
                @if ($canEditGradeBook)
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#templateApplyModal"><i class="fas fa-layer-group mr-1"></i>{{ $selectedTemplate ? 'Aplicar plantilla de materia' : 'Aplicar plantilla' }}</button>
                    <button type="button" class="btn btn-outline-warning btn-sm" data-toggle="modal" data-target="#categoriesModal"><i class="fas fa-sync-alt mr-1"></i>Categorias configuradas</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#importModal"><i class="fas fa-file-import mr-1"></i>Importacion masiva</button>
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#categoryModal"><i class="fas fa-plus mr-1"></i>{{ $editCategory ? 'Editar categoria' : 'Nueva categoria' }}</button>
                @endif
            </div>
        </div>
    </div>

    <div class="card collector-table-card {{ $readOnlyGradeBook ? 'collector-readonly' : '' }}">
        <div class="card-header">
            <h3 class="card-title">{{ $selectedPeriodType === 'exam' ? $selectedPeriodLabel : 'Colector de notas' }}</h3>
            <div class="card-tools text-muted small">
                @if ($selectedPeriodType === 'trimester')
                    Total configurado: {{ rtrim(rtrim(number_format($gradeBoard['percentage_total'], 2), '0'), '.') }}% | {{ $gradeBoard['can_calculate_report'] ? 'Report Card activo' : 'Falta completar 100%' }}
                @else
                    Captura separada del periodo anual.
                @endif
            </div>
        </div>
        <div class="card-body">
            @if ($selectedPeriodType === 'trimester' && $categories->count() === 0)
                @if ($selectedTemplate)
                    <div class="alert alert-info m-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: .75rem;">
                        <div>
                            Materia tiene plantilla <strong>{{ $selectedTemplate['name'] }}</strong>, pero aun no se ha aplicado a este trimestre.
                            @if ($canEditGradeBook)
                                Presiona boton para crear categorias y completar 100%.
                            @else
                                Esta vista es solo de consulta.
                            @endif
                        </div>
                        @if ($canEditGradeBook)
                            <form method="POST" action="/pad/notas/plantillas/aplicar" class="mb-0">
                                @csrf
                                <input type="hidden" name="asignacion_id" value="{{ $selectedAssignmentId }}">
                                <input type="hidden" name="trimestre_id" value="{{ $selectedTrimesterId }}">
                                <input type="hidden" name="template_key" value="{{ $selectedTemplateKey }}">
                                <button class="btn btn-secondary btn-sm">
                                    <i class="fas fa-layer-group mr-1"></i>Aplicar {{ $selectedTemplate['name'] }}
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="alert alert-info m-3">Crea categorias primero. Luego podras capturar notas y conducta por alumno.</div>
                @endif
            @elseif ($selectedPeriodType === 'trimester')
                <form method="POST" action="/pad/notas/calificaciones">
                    @csrf
                    <input type="hidden" name="asignacion_id" value="{{ $selectedAssignmentId }}">
                    <input type="hidden" name="trimestre_id" value="{{ $selectedTrimesterId }}">
                    <input type="hidden" name="periodo" value="{{ $selectedPeriod }}">

                    <div class="collector-grid">
                        <table class="table table-bordered table-sm" id="collector-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th rowspan="2" class="collector-sticky-number">#</th>
                                            <th rowspan="2" class="collector-sticky-student">Alumno</th>
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
                                            <td class="collector-sticky-number">{{ $row['id'] }}</td>
                                            <td class="collector-sticky-student">{{ $row['nombre'] }}</td>
                                            @foreach ($gradeBoard['categories'] as $category)
                                                @php($categoryScore = $row['categories'][$category->id] ?? [])
                                                <td class="text-center">
                                                    <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_1]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_1', $categoryScore['nota_1'] ?? '') }}" @disabled($readOnlyGradeBook)>
                                                </td>
                                                @if ($category->tipo_calculo === 'normal')
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_2]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_2', $categoryScore['nota_2'] ?? '') }}" @disabled($readOnlyGradeBook)>
                                                    </td>
                                                @endif
                                                <td class="final-cell">{{ $categoryScore['promedio_1'] ?? '-' }}</td>
                                                @if ($category->tipo_calculo === 'normal')
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_3]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_3', $categoryScore['nota_3'] ?? '') }}" @disabled($readOnlyGradeBook)>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_4]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_4', $categoryScore['nota_4'] ?? '') }}" @disabled($readOnlyGradeBook)>
                                                    </td>
                                                @else
                                                    <td class="text-center">
                                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $row['id'] }}][{{ $category->id }}][nota_2]" class="form-control form-control-sm grade-input" value="{{ old('grades.'.$row['id'].'.'.$category->id.'.nota_2', $categoryScore['nota_2'] ?? '') }}" @disabled($readOnlyGradeBook)>
                                                    </td>
                                                @endif
                                                <td class="final-cell">{{ $categoryScore['promedio_2'] ?? '-' }}</td>
                                            @endforeach
                                            <td class="final-cell">{{ $row['progress_1'] }}</td>
                                            <td class="final-cell">{{ $row['progress_2'] }}</td>
                                            <td class="final-cell">{{ $row['report_card'] ?? '-' }}</td>
                                            <td class="text-center">
                                                <input type="number" step="0.01" min="0" max="100" name="conduct[{{ $row['id'] }}]" class="form-control form-control-sm grade-input" value="{{ old('conduct.'.$row['id'], $row['conducta'] ?? '') }}" @disabled($readOnlyGradeBook)>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                        </table>
                    </div>

                    @if ($canEditGradeBook)
                        <div class="p-3 border-top">
                            <button class="btn btn-success btn-sm">Guardar colector</button>
                        </div>
                    @endif
                </form>
            @else
                <form method="POST" action="/pad/notas/calificaciones">
                    @csrf
                    <input type="hidden" name="asignacion_id" value="{{ $selectedAssignmentId }}">
                    <input type="hidden" name="trimestre_id" value="{{ $selectedTrimesterId }}">
                    <input type="hidden" name="periodo" value="{{ $selectedPeriod }}">

                    <div class="p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-2" style="gap:.75rem;">
                            <h4 class="mb-0" style="font-size:1rem;">{{ $periodExamMeta['label'] }}</h4>
                            <small class="text-muted">Captura separada del trimestre. Este examen aporta {{ $periodExamMeta['weight'] }}% al promedio anual de la materia.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width:72px;">#</th>
                                        <th>Alumno</th>
                                        <th class="text-center" style="width:180px;">{{ $periodExamMeta['label'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($gradeBoard['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['id'] }}</td>
                                        <td>{{ $row['nombre'] }}</td>
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100" name="period_exam[{{ $row['id'] }}]" class="form-control form-control-sm grade-input mx-auto" value="{{ old('period_exam.'.$row['id'], $row['period_exam'] ?? '') }}" @disabled($readOnlyGradeBook)>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($canEditGradeBook)
                        <div class="p-3 border-top">
                            <button class="btn btn-success btn-sm">Guardar {{ $periodExamMeta['label'] }}</button>
                        </div>
                    @endif
                </form>
            @endif

            <div class="border-top p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-2" style="gap:.75rem;">
                        <h4 class="mb-0" style="font-size:1rem;">Resumen anual preliminar</h4>
                        <small class="text-muted">Promedio por alumno para esta materia. Usa T1-T4 20% c/u, examen semestral 10% y examen final 10%.</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width:72px;">#</th>
                                    <th>Alumno</th>
                                    <th class="text-center">Avance anual</th>
                                    <th class="text-center">Peso capturado</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($gradeBoard['rows'] as $row)
                                <tr>
                                    <td>{{ $row['id'] }}</td>
                                    <td>{{ $row['nombre'] }}</td>
                                    <td class="text-center">
                                        @if ($row['annual_average'] !== null)
                                            <span class="score-badge {{ $row['annual_average'] >= 85 ? 'score-high' : ($row['annual_average'] >= 70 ? 'score-mid' : 'score-low') }}">{{ $row['annual_average'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $row['annual_captured_weight'] }}%</td>
                                    <td class="text-center">
                                        @if ($row['annual_average_final'] !== null)
                                            <span class="badge badge-success">Completo</span>
                                        @elseif ($row['annual_average'] !== null)
                                            <span class="badge badge-warning">Preliminar</span>
                                        @else
                                            <span class="badge badge-secondary">Sin datos</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>
@endif

@if ($canEditGradeBook)
<div class="modal fade" id="templateApplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aplicar plantilla de categorias</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/pad/notas/plantillas/aplicar">
                    @csrf
                    <input type="hidden" name="asignacion_id" value="{{ $selectedAssignmentId }}">
                    <input type="hidden" name="trimestre_id" value="{{ $selectedTrimesterId }}">

                    <div class="form-group">
                        <label>Plantilla</label>
                        <select name="template_key" class="form-control" required>
                            @foreach ($categoryTemplates as $templateKey => $template)
                                <option value="{{ $templateKey }}" @selected($selectedTemplateKey === $templateKey)>{{ $template['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    @foreach ($categoryTemplates as $templateKey => $template)
                        <div class="config-note mb-3 template-preview-block" data-template-preview="{{ $templateKey }}" style="{{ $selectedTemplateKey === $templateKey ? '' : 'display:none;' }}">
                            <div class="font-weight-bold mb-1">{{ $template['name'] }}</div>
                            <div class="text-muted small mb-2">{{ $template['description'] ?? 'Sin descripcion.' }}</div>
                            <ul class="mb-0 pl-3">
                                @foreach ($template['categories'] as $templateCategory)
                                    <li>{{ $templateCategory['nombre'] }} | {{ rtrim(rtrim(number_format($templateCategory['porcentaje'], 2), '0'), '.') }}% | {{ $templateCategory['tipo_calculo'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="reemplazar_existentes" name="reemplazar_existentes" value="1" @checked(old('reemplazar_existentes'))>
                        <label class="form-check-label" for="reemplazar_existentes">Reemplazar categorias activas existentes del trimestre seleccionado</label>
                    </div>

                    <div class="text-right">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-secondary btn-sm">Aplicar plantilla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const templateSelect = document.querySelector('select[name="template_key"]');

        const syncTemplatePreview = function () {
            if (!templateSelect) {
                return;
            }

            document.querySelectorAll('[data-template-preview]').forEach(function (preview) {
                preview.style.display = preview.dataset.templatePreview === templateSelect.value ? '' : 'none';
            });
        };

        if (templateSelect) {
            templateSelect.addEventListener('change', syncTemplatePreview);
            syncTemplatePreview();
        }

        @if ($canEditGradeBook && $editCategory)
            $('#categoryModal').modal('show');
        @endif
    });
</script>
@endpush
