@extends('layouts.panel')

@section('title', 'Correos')

@section('content')
@php
    $emailStudentSectionId = collect($studentsForEmail)->firstWhere('id', (int) old('alumno_id', $editEmail->alumno_id ?? 0))->seccion_id ?? '';
    $emailFormVisible = $editEmail !== null || old('padre_id') !== null || old('alumno_id') !== null || old('trimestre_id') !== null || old('estado') !== null;
    $templateFormVisible = $editTemplate !== null || old('nombre') !== null || old('asunto') !== null || old('cuerpo_html') !== null;
    $selectedTemplateRoles = collect(old('roles', $editTemplate?->roles?->pluck('id')->all() ?? []))->map(fn ($value) => (int) $value)->all();
    $selectedTemplateDocuments = collect(old('documentos_generados', $editTemplate->documentos_generados ?? []))->map(fn ($value) => (string) $value)->all();
@endphp

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-envelope"></i>
                <span>Gestion de correos</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-primary btn-sm" type="button" data-toggle="modal" data-target="#emailFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editEmail ? 'Editar correo' : 'Nuevo correo' }}
                </button>
                <button class="btn btn-info btn-sm" type="button" data-toggle="modal" data-target="#emailBatchModal">
                    <i class="fas fa-paper-plane mr-1"></i>Envio masivo
                </button>
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#emailTemplateModal">
                    <i class="fas fa-file-alt mr-1"></i>{{ $editTemplate ? 'Editar plantilla' : 'Nueva plantilla' }}
                </button>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ \App\Support\AppUrl::route('emails.index') }}" class="card maint-card">
    <div class="card-body">
        <div class="filter-toolbar">
            <div class="form-group"><label>Lote</label><select name="lote_id" class="form-control"><option value="">Todos los lotes</option>@foreach ($emailBatches as $batch)<option value="{{ $batch->id }}" @selected($emailFilters['lote_id'] === $batch->id)>#{{ $batch->id }} · {{ $batch->template->nombre ?? 'Plantilla' }}</option>@endforeach</select></div>
            <div class="form-group"><label>Seccion</label><select name="seccion_id" class="form-control"><option value="">Todas las secciones</option>@foreach ($studentSections as $section)<option value="{{ $section->id }}" @selected($emailFilters['seccion_id'] === $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>@endforeach</select></div>
            <div class="form-group"><label>Desde</label><input type="date" name="fecha_desde" class="form-control" value="{{ $emailFilters['fecha_desde'] }}"></div>
            <div class="form-group"><label>Hasta</label><input type="date" name="fecha_hasta" class="form-control" value="{{ $emailFilters['fecha_hasta'] }}"></div>
            <div class="form-group"><button class="btn btn-outline-primary"><i class="fas fa-filter mr-1"></i>Filtrar</button> <a class="btn btn-default" href="{{ \App\Support\AppUrl::route('emails.index') }}">Limpiar</a></div>
        </div>
    </div>
</form>

<div class="card maint-card">
    <div class="card-header border-0">
        <h3 class="card-title">Lotes de envio recientes</h3>
        <div class="card-tools text-muted small">Los correos se procesan en segundo plano.</div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover maint-table mb-0">
            <thead class="bg-light"><tr><th>#</th><th>Plantilla</th><th>Seccion</th><th>Trimestre</th><th>Progreso</th><th>Resultado</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse ($emailBatches as $batch)
                @php($percent = $batch->total > 0 ? (int) round(($batch->procesados / $batch->total) * 100) : 0)
                <tr>
                    <td>{{ $batch->id }}</td>
                    <td>{{ $batch->template->nombre ?? 'Plantilla eliminada' }}</td>
                    <td>{{ ($batch->section->grado ?? '').' '.($batch->section->nombre ?? '') }}</td>
                    <td>{{ $batch->trimestre }}</td>
                    <td style="min-width:160px;">
                        <div class="progress progress-xs mb-1"><div class="progress-bar {{ $batch->estado === 'completado_con_errores' ? 'bg-warning' : 'bg-info' }}" style="width:{{ $percent }}%"></div></div>
                        <small>{{ $batch->procesados }}/{{ $batch->total }} procesados ({{ $percent }}%)</small>
                    </td>
                    <td><span class="text-success">{{ $batch->enviados }} enviados</span> · <span class="text-danger">{{ $batch->fallidos }} fallidos</span>@if($batch->omitidos)<br><small class="text-muted">{{ $batch->omitidos }} omitidos</small>@endif</td>
                    <td>
                        @if ($batch->estado === 'procesando')<span class="badge badge-info">En proceso</span>
                        @elseif ($batch->estado === 'completado')<span class="badge badge-success">Completado</span>
                        @else<span class="badge badge-warning">Con errores</span>
                        @endif
                    </td>
                    <td class="maint-actions-cell">
                        <a class="btn btn-xs btn-outline-success" title="Exportar resultados" href="{{ \App\Support\AppUrl::route('emails.batches.export', ['batch' => $batch->id]) }}"><i class="fas fa-file-csv"></i></a>
                        @if ($batch->fallidos > 0 && $batch->estado !== 'procesando')
                            <form method="POST" action="{{ \App\Support\AppUrl::route('emails.batches.retry', ['batch' => $batch->id]) }}" data-swal-confirm="true" data-swal-title="Reintentar correos fallidos" data-swal-text="Solo se enviaran nuevamente los correos fallidos de este lote." data-swal-confirm-label="Si, reintentar">@csrf<button class="btn btn-xs btn-warning" title="Reintentar fallidos"><i class="fas fa-redo"></i></button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Aun no hay lotes masivos.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card maint-card">
            <div class="card-header border-0">
                <h3 class="card-title">Plantillas configuradas</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover maint-table">
                    <thead class="bg-light"><tr><th>#</th><th>Nombre</th><th>Perfiles</th><th>Adjuntos</th><th>Asunto</th><th>Vista previa</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($templateCatalog as $template)
                        <tr>
                            <td>{{ $template->id }}</td>
                            <td><strong>{{ $template->nombre }}</strong></td>
                            <td>
                                @if ($template->roles->isEmpty())
                                    <span class="badge badge-secondary">Todos</span>
                                @else
                                    <div class="d-flex flex-wrap" style="gap:.25rem;">
                                        @foreach ($template->roles as $role)
                                            <span class="badge badge-info">{{ $role->nombre }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @php($documentLabels = collect($template->documentos_generados ?? [])->map(fn ($code) => $generatedDocumentLabels[$code] ?? $code))
                                @if ($documentLabels->isEmpty())
                                    <span class="text-muted">Sin adjuntos</span>
                                @else
                                    <div class="d-flex flex-column">
                                        @foreach ($documentLabels as $label)
                                            <span>{{ $label }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>{{ $template->asunto }}</td>
                            <td><div style="max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ strip_tags($template->cuerpo_html) }}</div></td>
                            <td class="maint-actions-cell">
                                <a href="{{ \App\Support\AppUrl::route('emails.index') }}?edit_template={{ $template->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ \App\Support\AppUrl::route('emails.templates.destroy', ['template' => $template->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar plantilla" data-swal-text="La plantilla ya no aparecera en nuevos envios, pero no se eliminara fisicamente." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay plantillas activas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card maint-card">
            <div class="card-header">
                <h3 class="card-title">Historial de correos</h3>
                <div class="card-tools w-100 mt-2 mt-md-0">
                    <div class="filter-toolbar justify-content-md-end" data-filter-target="emails-table">
                        <div class="form-group">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Familiar, alumno o plantilla" data-filter-name="text">
                        </div>
                        <div class="form-group">
                            <label class="small text-muted mb-1">Estado</label>
                            <select class="form-control form-control-sm" data-filter-name="status">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="enviado">Enviado</option>
                                <option value="fallido">Fallido</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small text-muted mb-1">Trimestre</label>
                            <select class="form-control form-control-sm" data-filter-name="trimester">
                                <option value="">Todos</option>
                                @foreach ($trimesters as $trimester)
                                    <option value="{{ strtolower($trimester->nombre) }}">{{ $trimester->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover maint-table" id="emails-table">
                    <thead class="bg-light"><tr><th>#</th><th>Familiar</th><th>Alumno</th><th>Plantilla</th><th>Adjuntos</th><th>Trimestre</th><th>Estado</th><th>Resultado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($emails as $email)
                        <tr data-filter-row data-text="{{ strtolower($email->nombres.' '.$email->apellidos.' '.$email->email_principal.' '.$email->alumno_nombres.' '.$email->alumno_apellidos.' '.$email->plantilla.' '.($email->parentesco ?? '')) }}" data-status="{{ strtolower($email->estado) }}" data-trimester="{{ strtolower($email->trimestre) }}">
                            <td>{{ $email->id }}</td>
                            <td><strong>{{ $email->nombres }} {{ $email->apellidos }}</strong><br><small>{{ $email->email_principal }}{{ $email->parentesco ? ' | '.$email->parentesco : '' }}</small></td>
                            <td>{{ $email->alumno_nombres }} {{ $email->alumno_apellidos }}</td>
                            <td>{{ $email->plantilla }}</td>
                            <td>
                                @php($dispatchDocuments = collect($email->adjuntos_generados ?? [])->map(fn ($code) => $generatedDocumentLabels[$code] ?? $code))
                                @if ($dispatchDocuments->isEmpty())
                                    <span class="text-muted">Sin adjuntos</span>
                                @else
                                    @foreach ($dispatchDocuments as $label)
                                        <div>{{ $label }}</div>
                                    @endforeach
                                @endif
                            </td>
                            <td>{{ $email->trimestre }}</td>
                            <td>
                                @if ($email->en_cola)
                                    <span class="badge badge-info">En cola</span>
                                @elseif ($email->estado === 'enviado')
                                    <span class="badge badge-success">Enviado</span>
                                @elseif ($email->estado === 'pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                @else
                                    <span class="badge badge-danger">Fallido</span>
                                @endif
                            </td>
                            <td>
                                @if ($email->estado === 'enviado')
                                    <small class="text-success">{{ $email->destinatario_email ?? $email->email_principal }}</small>
                                    @if ($email->enviado_en)
                                        <div class="text-muted small">{{ \Illuminate\Support\Carbon::parse($email->enviado_en)->format('d/m/Y H:i') }}</div>
                                    @endif
                                @elseif ($email->error_mensaje)
                                    <small class="text-danger">{{ \Illuminate\Support\Str::limit($email->error_mensaje, 90) }}</small>
                                @else
                                    <small class="text-muted">Listo para envio</small>
                                @endif
                            </td>
                            <td class="maint-actions-cell">
                                @if (! $email->en_cola)
                                    <form method="POST" action="{{ \App\Support\AppUrl::route('emails.send', ['dispatch' => $email->id]) }}">@csrf<button class="btn btn-xs btn-info" title="Enviar en segundo plano"><i class="fas fa-paper-plane"></i></button></form>
                                @endif
                                @if (! $email->en_cola)<a href="{{ \App\Support\AppUrl::route('emails.index') }}?edit_email={{ $email->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>@endif
                                <form method="POST" action="{{ \App\Support\AppUrl::route('emails.destroy', ['dispatch' => $email->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar registro de correo" data-swal-text="El historial quedara oculto del mantenimiento, pero no se eliminara fisicamente." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No hay correos registrados.</td></tr>
                    @endforelse
                    @if ($emails->count() > 0)
                        <tr data-empty-filter style="display:none;">
                            <td colspan="9" class="text-center text-muted">No se encontraron correos con esos filtros.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailBatchModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear envio masivo</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="{{ \App\Support\AppUrl::route('emails.batches.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2">Se creara un envio por cada familiar con correo valido vinculado a alumnos de la seccion. El sistema enviara los correos en segundo plano.</div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Plantilla</label>
                            <select name="plantilla_id" class="form-control" required>
                                <option value="">Seleccione una plantilla</option>
                                @foreach ($templates as $template)<option value="{{ $template->id }}">{{ $template->nombre }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Seccion</label>
                            <select name="seccion_id" class="form-control" required>
                                <option value="">Seleccione una seccion</option>
                                @foreach ($studentSections as $section)<option value="{{ $section->id }}">{{ $section->grado }} {{ $section->nombre }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Trimestre</label>
                            <select name="trimestre_id" class="form-control" required>
                                @foreach ($trimesters as $trimester)<option value="{{ $trimester->id }}">{{ $trimester->nombre }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="batchResend" name="reenviar" value="1">
                        <label class="custom-control-label" for="batchResend">Reenviar incluso a destinatarios que ya recibieron esta plantilla en este trimestre.</label>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button><button class="btn btn-info"><i class="fas fa-paper-plane mr-1"></i>Crear lote de envio</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="emailFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editEmail ? 'Editar registro de correo' : 'Nuevo registro de correo' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editEmail ? \App\Support\AppUrl::route('emails.update', ['dispatch' => $editEmail->id]) : \App\Support\AppUrl::route('emails.store') }}">
                    @csrf
                    @if ($editEmail) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Plantilla</label>
                            <select name="plantilla_id" class="form-control" required>
                                <option value="">Seleccione una plantilla</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('plantilla_id', $editEmail->plantilla_id ?? '') == $template->id)>{{ $template->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Solo aparecen las plantillas permitidas para tu perfil.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Familiar</label>
                            <select name="padre_id" class="form-control" required>
                                <option value="">Seleccione un familiar</option>
                                @foreach ($familyMembers as $familyMember)
                                    <option value="{{ $familyMember->id }}" @selected(old('padre_id', $editEmail->padre_id ?? '') == $familyMember->id)>{{ $familyMember->nombres }} {{ $familyMember->apellidos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Seccion</label>
                            <select class="form-control" id="email-section-filter">
                                <option value="">Todas</option>
                                @foreach ($studentSections as $section)
                                    <option value="{{ $section->id }}" @selected((string) $emailStudentSectionId === (string) $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Alumno</label>
                            <select name="alumno_id" class="form-control" id="email-student-filter" required>
                                <option value="">Seleccione un alumno</option>
                                @foreach ($studentsForEmail as $student)
                                    <option value="{{ $student->id }}" data-section-id="{{ $student->seccion_id }}" @selected(old('alumno_id', $editEmail->alumno_id ?? '') == $student->id)>{{ $student->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Trimestre</label>
                            <select name="trimestre_id" class="form-control" required>
                                @foreach ($trimesters as $trimester)
                                    <option value="{{ $trimester->id }}" @selected(old('trimestre_id', $editEmail->trimestre_id ?? '') == $trimester->id)>{{ $trimester->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Estado</label>
                            <select name="estado" class="form-control" required>
                                @foreach (['pendiente', 'enviado', 'fallido'] as $status)
                                    <option value="{{ $status }}" @selected(old('estado', $editEmail->estado ?? 'pendiente') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="email-preview-button"><i class="fas fa-eye mr-1"></i>Vista previa</button>
                    <button class="btn btn-primary btn-sm">{{ $editEmail ? 'Guardar cambios' : 'Agregar registro' }}</button>
                    @if ($editEmail)<a href="{{ \App\Support\AppUrl::route('emails.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emailTemplateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editTemplate ? 'Editar plantilla de correo' : 'Nueva plantilla de correo' }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editTemplate ? \App\Support\AppUrl::route('emails.templates.update', ['template' => $editTemplate->id]) : \App\Support\AppUrl::route('emails.templates.store') }}">
                    @csrf
                    @if ($editTemplate) @method('PATCH') @endif
                    <div class="form-group">
                        <label>Nombre interno</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $editTemplate->nombre ?? '') }}" placeholder="reporte_trimestral" required>
                    </div>
                    <div class="form-group">
                        <label>Descripcion</label>
                        <input type="text" name="descripcion" class="form-control" value="{{ old('descripcion', $editTemplate->descripcion ?? '') }}" placeholder="Plantilla para envio de boletines y avisos">
                    </div>
                    <div class="form-group">
                        <label>Asunto</label>
                        <input type="text" name="asunto" class="form-control" value="{{ old('asunto', $editTemplate->asunto ?? '') }}" placeholder="Reporte de notas" required>
                    </div>
                    <div class="form-group">
                        <label>Perfiles autorizados</label>
                        <select name="roles[]" class="form-control" multiple size="5">
                            @foreach ($rolesCatalog as $role)
                                <option value="{{ $role->id }}" @selected(in_array((int) $role->id, $selectedTemplateRoles, true))>{{ $role->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Si no seleccionas perfiles, la plantilla quedara disponible para todos los usuarios con acceso al modulo.</small>
                    </div>
                    <div class="form-group">
                        <label>Documentos generados a adjuntar</label>
                        <select name="documentos_generados[]" class="form-control" multiple size="4">
                            @foreach ($generatedDocumentCatalog as $document)
                                <option value="{{ $document['code'] }}" @selected(in_array($document['code'], $selectedTemplateDocuments, true))>{{ $document['label'] }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Estos adjuntos no se suben manualmente: el sistema los renderiza al momento de enviar.</small>
                    </div>
                    <div class="form-group mb-2">
                        <label>Cuerpo HTML</label>
                        <textarea name="cuerpo_html" rows="8" class="form-control" placeholder="<h1>Hola</h1><p>Contenido...</p>" required>{{ old('cuerpo_html', $editTemplate->cuerpo_html ?? '') }}</textarea>
                        <small class="text-muted">Puedes usar HTML basico. Variables disponibles: {{'{{familiar_nombre}}'}}, {{'{{alumno_nombre}}'}}, {{'{{trimestre}}'}}, {{'{{perfil}}'}}, {{'{{app_nombre}}'}}, {{'{{app_url}}'}}.</small>
                    </div>
                    <button class="btn btn-success btn-sm">{{ $editTemplate ? 'Guardar plantilla' : 'Agregar plantilla' }}</button>
                    @if ($editTemplate)<a href="{{ \App\Support\AppUrl::route('emails.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if ($emailFormVisible)
        $('#emailFormModal').modal('show');
        @endif

        @if ($templateFormVisible)
        $('#emailTemplateModal').modal('show');
        @endif

        const sectionSelect = document.getElementById('email-section-filter');
        const studentSelect = document.getElementById('email-student-filter');
        const previewButton = document.getElementById('email-preview-button');

        if (previewButton) {
            previewButton.addEventListener('click', function () {
                const templateId = document.querySelector('select[name="plantilla_id"]')?.value;
                const guardianId = document.querySelector('select[name="padre_id"]')?.value;
                const studentId = document.querySelector('select[name="alumno_id"]')?.value;
                const trimesterId = document.querySelector('select[name="trimestre_id"]')?.value;

                if (!templateId || !guardianId || !studentId || !trimesterId) {
                    Swal.fire({ icon: 'info', title: 'Completa los datos', text: 'Selecciona plantilla, familiar, alumno y trimestre para generar una vista previa.' });
                    return;
                }

                const params = new URLSearchParams({ plantilla_id: templateId, padre_id: guardianId, alumno_id: studentId, trimestre_id: trimesterId });
                window.open('{{ \App\Support\AppUrl::route('emails.preview') }}?' + params.toString(), '_blank', 'noopener');
            });
        }

        if (!sectionSelect || !studentSelect) return;

        const syncStudents = function () {
            const selectedSectionId = sectionSelect.value;
            let selectedStudentStillVisible = false;

            Array.from(studentSelect.options).forEach(function (option, index) {
                if (index === 0) {
                    option.hidden = false;
                    return;
                }

                const visible = !selectedSectionId || option.dataset.sectionId === selectedSectionId;
                option.hidden = !visible;

                if (visible && option.value === studentSelect.value) {
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
