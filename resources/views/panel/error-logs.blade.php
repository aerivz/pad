@extends('layouts.panel')

@section('title', 'Errores del sistema')

@section('content')
<style>
    .error-log-filters { display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end; }
    .error-log-entry { border: 1px solid #e2e8f0; border-radius: .75rem; padding: .9rem 1rem; background: #fff; }
    .error-log-entry + .error-log-entry { margin-top: .75rem; }
    .error-log-meta { display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: center; color: #64748b; font-size: .78rem; }
    .error-log-message { margin: .65rem 0 0; color: #1f2937; overflow-wrap: anywhere; }
    .error-log-details summary { margin-top: .65rem; color: #820005; font-weight: 700; cursor: pointer; }
    .error-log-details pre { max-height: 420px; overflow: auto; margin: .65rem 0 0; padding: .85rem; border-radius: .5rem; background: #111827; color: #e5e7eb; font-size: .72rem; white-space: pre-wrap; overflow-wrap: anywhere; }
    body.dark-theme .error-log-entry { background: #182338; border-color: #2f405b; }
    body.dark-theme .error-log-message { color: #edf4fc; }
    body.dark-theme .error-log-meta { color: #aebed1; }
    body.dark-theme .error-log-details summary { color: #ff9ba7; }
    @media (max-width: 767.98px) {
        .error-log-filters { grid-template-columns: 1fr; }
    }
</style>

<div class="card maint-card">
    <div class="card-header border-0">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>Consulta de errores</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ \App\Support\AppUrl::route('error-logs.index') }}" class="error-log-filters">
            <div class="form-group mb-0">
                <label>Desde</label>
                <input type="date" name="desde" required class="form-control" value="{{ $errorLogFilters['desde'] }}">
            </div>
            <div class="form-group mb-0">
                <label>Hasta</label>
                <input type="date" name="hasta" required class="form-control" value="{{ $errorLogFilters['hasta'] }}">
            </div>
            <button class="btn btn-primary" style="background:#820005;border-color:#820005;"><i class="fas fa-search mr-1"></i>Consultar</button>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <strong>{{ $errorLogTotal }} {{ $errorLogTotal === 1 ? 'error encontrado' : 'errores encontrados' }}</strong>
    <small class="text-muted">Se muestran errores, críticos, alertas y emergencias.</small>
</div>

@if ($errorLogTruncated)
    <div class="alert alert-warning">Hay más resultados. Se muestran los 250 errores más recientes del rango seleccionado.</div>
@endif

@forelse ($errorLogEntries as $entry)
    <article class="error-log-entry">
        <div class="error-log-meta">
            <span class="badge badge-danger">{{ $entry['level'] }}</span>
            <span><i class="far fa-clock mr-1"></i>{{ $entry['timestamp'] }}</span>
            <span><i class="fas fa-server mr-1"></i>{{ $entry['environment'] }}</span>
            <span><i class="far fa-file-alt mr-1"></i>{{ $entry['file'] }}</span>
        </div>
        <p class="error-log-message">{{ $entry['message'] }}</p>
        <details class="error-log-details">
            <summary>Ver detalle técnico</summary>
            <pre>{{ $entry['details'] }}</pre>
        </details>
    </article>
@empty
    <div class="card maint-card">
        <div class="card-body text-center text-muted py-5">
            <i class="far fa-check-circle text-success fa-3x mb-3"></i>
            <h5>Sin errores en este rango</h5>
            <p class="mb-0">No se encontraron entradas de error entre las fechas seleccionadas.</p>
        </div>
    </div>
@endforelse
@endsection
