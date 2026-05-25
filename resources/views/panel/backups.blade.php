@extends('layouts.panel')

@section('title', 'Backups')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h3 class="card-title mb-1">Respaldos del sistema</h3>
                    <p class="text-muted small mb-0">Genera un respaldo en segundo plano con base de datos, uploads e imagenes del sistema.</p>
                </div>
                <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                    <a href="{{ route('backups.index') }}" class="btn btn-info btn-sm"><i class="fas fa-sync-alt mr-1"></i>Actualizar</a>
                    <form method="POST" action="{{ route('backups.store') }}" class="d-inline-block">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="fas fa-file-archive mr-1"></i>Generar backup</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="config-note mb-3">
                    <strong>Incluye:</strong> base de datos en SQL, carpeta <code>public/uploads</code> y carpeta <code>public/images</code>.
                    <br>
                    <strong>Segundo plano:</strong> el proceso usa cola <code>database</code>; la tabla se actualiza cuando el ZIP termina.
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Solicitado por</th>
                                <th>Estado</th>
                                <th>Archivos</th>
                                <th>Tamano</th>
                                <th>Solicitado</th>
                                <th>Finalizado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($backups as $backup)
                            <tr>
                                <td>{{ $backup->id }}</td>
                                <td>
                                    <strong>{{ $backup->nombre }}</strong>
                                    @if ($backup->error_message)
                                        <br><small class="text-danger">{{ $backup->error_message }}</small>
                                    @endif
                                </td>
                                <td>{{ trim(($backup->user->nombres ?? '').' '.($backup->user->apellidos ?? '')) ?: ($backup->user->nombre_usuario ?? 'Sistema') }}</td>
                                <td>
                                    @if ($backup->estado === 'completado')
                                        <span class="badge badge-success">Completado</span>
                                    @elseif ($backup->estado === 'procesando')
                                        <span class="badge badge-primary">Procesando</span>
                                    @elseif ($backup->estado === 'fallido')
                                        <span class="badge badge-danger">Fallido</span>
                                    @else
                                        <span class="badge badge-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td>{{ $backup->total_archivos ?? '—' }}</td>
                                <td>{{ $backup->tamano_bytes ? number_format($backup->tamano_bytes / 1048576, 2).' MB' : '—' }}</td>
                                <td>{{ optional($backup->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($backup->finished_at)->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="action-cell">
                                    @if ($backup->isReady())
                                        <a href="{{ route('backups.download', $backup) }}" class="btn btn-xs btn-success">Descargar ZIP</a>
                                    @else
                                        <span class="text-muted small">Esperando archivo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Aun no hay backups generados.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if ($hasPendingBackups)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.setTimeout(function () {
            window.location.reload();
        }, 12000);
    });
</script>
@endif
@endpush
