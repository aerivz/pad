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
                <select name="seccion_id" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($reportSections as $section)
                        <option value="{{ $section->id }}" @selected(($reportFilters['seccion_id'] ?? null) == $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Alumno</label>
                <select name="alumno_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($reportStudents as $student)
                        <option value="{{ $student->id }}" @selected(($reportFilters['alumno_id'] ?? null) == $student->id)>{{ $student->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Trimestre</label>
                <select name="trimestre_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($reportTrimesters as $trimester)
                        <option value="{{ $trimester->id }}" @selected(($reportFilters['trimestre_id'] ?? null) == $trimester->id)>{{ $trimester->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-outline-primary btn-block">Ver</button>
            </div>
            <div class="col-md-12 mt-3 d-flex justify-content-end">
                @if (! empty($reportFilters['alumno_id']) || ! empty($reportFilters['seccion_id']))
                    <a
                        href="{{ route('reportcard.pdf', ['seccion_id' => $reportFilters['seccion_id'] ?? null, 'alumno_id' => $reportFilters['alumno_id'] ?? null]) }}"
                        class="btn btn-danger"
                        target="_blank"
                    >
                        {{ ! empty($reportFilters['alumno_id']) ? 'Descargar PDF anual' : 'Descargar PDF de seccion' }}
                    </a>
                @else
                    <button type="button" class="btn btn-danger" disabled>Selecciona seccion o alumno para PDF</button>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Consolidado por estudiante</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead class="bg-light">
                <tr>
                    <th>Alumno</th>
                    <th>Seccion</th>
                    @foreach ($subjectColumns as $column)
                        <th class="text-center">{{ $column }}</th>
                    @endforeach
                    <th class="text-center">Promedio</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($reportCard as $report)
                <tr>
                    <td><strong>{{ $report['alumno'] }}</strong></td>
                    <td>{{ $report['seccion'] }}</td>
                    @foreach ($subjectColumns as $column)
                        @php($value = $report['materias'][$column] ?? null)
                        <td class="text-center">
                            @if ($value !== null)
                                <span class="score-badge {{ $scoreClass($value) }}">{{ $value }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center"><span class="score-badge {{ $scoreClass($report['promedio']) }}">{{ $report['promedio'] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="{{ 3 + count($subjectColumns) }}" class="text-center text-muted">No hay datos para los filtros seleccionados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
