@extends('layouts.panel')

@section('title', 'Boletines')

@section('content')
@php($scoreClass = fn ($score) => $score >= 85 ? 'score-high' : ($score >= 70 ? 'score-mid' : 'score-low'))
<div class="card">
    <div class="card-header"><h3 class="card-title">Consulta de boletines</h3></div>
    <div class="card-body">
        <form method="GET" action="/pad/report-card" class="row">
            <div class="col-md-4">
                <label>Seccion</label>
                <select name="seccion_id" class="form-control" id="report-section-filter">
                    <option value="">Todas</option>
                    @foreach ($reportSections as $section)
                        <option value="{{ $section->id }}" @selected(($reportFilters['seccion_id'] ?? null) == $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Alumno</label>
                <select name="alumno_id" class="form-control" id="report-student-filter">
                    <option value="">Todos</option>
                    @foreach ($reportStudents as $student)
                        <option value="{{ $student->id }}" data-section-id="{{ $student->seccion_id }}" @selected(($reportFilters['alumno_id'] ?? null) == $student->id)>{{ $student->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Periodo</label>
                <select name="periodo" class="form-control">
                    @foreach ($reportPeriodOptions as $option)
                        <option value="{{ $option['value'] }}" @selected(($reportFilters['periodo'] ?? '') === $option['value'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-outline-primary btn-block">Ver</button>
            </div>
            <div class="col-md-12 mt-3 d-flex justify-content-end">
                @if (! empty($reportFilters['alumno_id']) || ! empty($reportFilters['seccion_id']))
                    <a
                        href="{{ route('reportcard.pdf', ['seccion_id' => $reportFilters['seccion_id'] ?? null, 'alumno_id' => $reportFilters['alumno_id'] ?? null, 'periodo' => $reportFilters['periodo'] ?? '']) }}"
                        class="btn btn-danger"
                        target="_blank"
                    >
                        {{ ! empty($reportFilters['alumno_id']) ? 'Descargar PDF' : 'Descargar PDF de sección' }}
                    </a>
                @else
                    <button type="button" class="btn btn-danger" disabled>Selecciona seccion o alumno para PDF</button>
                @endif
            </div>
        </form>
        <div class="mt-3 text-muted small">
            Vista actual: <strong>{{ $reportSelectedPeriodLabel }}</strong>.
        </div>
    </div>
</div>

@forelse ($reportReports as $report)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $report['student']['full_name'] }} · {{ $report['student']['section_name'] }}</h3>
        <div class="card-tools text-muted small">Titular: {{ $report['teacher_name'] }}</div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm mb-0">
            <thead class="bg-light">
                <tr>
                    <th rowspan="2">Materia</th>
                    @foreach ($report['terms'] as $term)
                        <th colspan="3" class="text-center">{{ $term->nombre }}</th>
                        @if ((int) $term->numero === 2)<th rowspan="2" class="text-center">Semestral</th>@endif
                        @if ((int) $term->numero === 4)<th rowspan="2" class="text-center">Final</th>@endif
                    @endforeach
                    @if ($report['is_annual'])<th rowspan="2" class="text-center">Nota anual</th>@endif
                </tr>
                <tr>
                    @foreach ($report['terms'] as $term)<th class="text-center">P1</th><th class="text-center">P2</th><th class="text-center">Prom.</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($report['subjects'] as $subject)
                    <tr>
                        <td><strong>{{ $subject['subject'] }}</strong><div class="small text-muted">{{ $subject['teacher'] }}</div></td>
                        @foreach ($report['terms'] as $term)
                            @php($termData = $subject['terms'][$term->numero] ?? [])
                            @foreach (['progress_1', 'progress_2', 'period_average'] as $field)
                                @php($value = $termData[$field] ?? null)
                                <td class="text-center">{{ $value ?? '-' }}</td>
                            @endforeach
                            @if ((int) $term->numero === 2)<td class="text-center">{{ $subject['semester_evaluation'] ?? '-' }}</td>@endif
                            @if ((int) $term->numero === 4)<td class="text-center">{{ $subject['final_evaluation'] ?? '-' }}</td>@endif
                        @endforeach
                        @if ($report['is_annual'])<td class="text-center font-weight-bold">{{ $subject['final_period'] ?? '-' }}</td>@endif
                    </tr>
                @endforeach
                <tr class="bg-light">
                    <td><strong>Conducta</strong></td>
                    @foreach ($report['terms'] as $term)
                        <td colspan="3" class="text-center font-weight-bold">{{ $report['homeroom'][$term->numero]['conduct'] ?? '-' }}</td>
                        @if ((int) $term->numero === 2)<td></td>@endif
                        @if ((int) $term->numero === 4)<td></td>@endif
                    @endforeach
                    @if ($report['is_annual'])<td class="text-center font-weight-bold">{{ $report['final_conduct'] ?? '-' }}</td>@endif
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <strong>Comentarios del titular:</strong>
        @forelse ($report['teacher_comments'] as $term => $comment)
            <div><span class="text-muted">Trimestre {{ $term }}:</span> {{ $comment }}</div>
        @empty
            <span class="text-muted">Sin comentarios para el período mostrado.</span>
        @endforelse
    </div>
</div>
@empty
<div class="card"><div class="card-body text-center text-muted">No hay datos para los filtros seleccionados.</div></div>
@endforelse
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sectionSelect = document.getElementById('report-section-filter');
        const studentSelect = document.getElementById('report-student-filter');

        if (!sectionSelect || !studentSelect) {
            return;
        }

        const syncStudents = function () {
            const selectedSectionId = sectionSelect.value;
            let selectedStudentStillVisible = false;

            Array.from(studentSelect.options).forEach(function (option, index) {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const belongsToSection = !selectedSectionId || option.dataset.sectionId === selectedSectionId;
                option.hidden = !belongsToSection;

                if (belongsToSection && option.value === studentSelect.value) {
                    selectedStudentStillVisible = true;
                }
            });

            if (!selectedStudentStillVisible) {
                studentSelect.value = '';
            }
        };

        sectionSelect.addEventListener('change', syncStudents);
        syncStudents();
    });
</script>
@endpush
