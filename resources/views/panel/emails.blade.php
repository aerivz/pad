@extends('layouts.panel')

@section('title', 'Correos')

@section('content')
@php
    $emailStudentSectionId = collect($studentsForEmail)->firstWhere('id', (int) old('alumno_id', $editEmail->alumno_id ?? 0))->seccion_id ?? '';
    $emailFormVisible = $editEmail !== null || old('padre_id') !== null || old('alumno_id') !== null || old('trimestre_id') !== null || old('estado') !== null;
    $templateFormVisible = $editTemplate !== null || old('nombre') !== null || old('asunto') !== null || old('cuerpo_html') !== null;
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
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#emailTemplateModal">
                    <i class="fas fa-file-alt mr-1"></i>{{ $editTemplate ? 'Editar plantilla' : 'Nueva plantilla' }}
                </button>
            </div>
        </div>
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
                    <thead class="bg-light"><tr><th>#</th><th>Nombre</th><th>Asunto</th><th>Vista previa</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($templateCatalog as $template)
                        <tr>
                            <td>{{ $template->id }}</td>
                            <td><strong>{{ $template->nombre }}</strong></td>
                            <td>{{ $template->asunto }}</td>
                            <td><div style="max-width:320px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ strip_tags($template->cuerpo_html) }}</div></td>
                            <td class="maint-actions-cell">
                                <a href="{{ \App\Support\AppUrl::route('emails.index') }}?edit_template={{ $template->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ \App\Support\AppUrl::route('emails.templates.destroy', ['template' => $template->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar plantilla" data-swal-text="La plantilla ya no aparecera en nuevos envios, pero no se eliminara fisicamente." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No hay plantillas activas.</td></tr>
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
                    <thead class="bg-light"><tr><th>#</th><th>Familiar</th><th>Alumno</th><th>Plantilla</th><th>Trimestre</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($emails as $email)
                        <tr data-filter-row data-text="{{ strtolower($email->nombres.' '.$email->apellidos.' '.$email->email_principal.' '.$email->alumno_nombres.' '.$email->alumno_apellidos.' '.$email->plantilla.' '.($email->parentesco ?? '')) }}" data-status="{{ strtolower($email->estado) }}" data-trimester="{{ strtolower($email->trimestre) }}">
                            <td>{{ $email->id }}</td>
                            <td><strong>{{ $email->nombres }} {{ $email->apellidos }}</strong><br><small>{{ $email->email_principal }}{{ $email->parentesco ? ' | '.$email->parentesco : '' }}</small></td>
                            <td>{{ $email->alumno_nombres }} {{ $email->alumno_apellidos }}</td>
                            <td>{{ $email->plantilla }}</td>
                            <td>{{ $email->trimestre }}</td>
                            <td>
                                @if ($email->estado === 'enviado')
                                    <span class="badge badge-success">Enviado</span>
                                @elseif ($email->estado === 'pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                @else
                                    <span class="badge badge-danger">Fallido</span>
                                @endif
                            </td>
                            <td class="maint-actions-cell">
                                <a href="{{ \App\Support\AppUrl::route('emails.index') }}?edit_email={{ $email->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ \App\Support\AppUrl::route('emails.destroy', ['dispatch' => $email->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar registro de correo" data-swal-text="El historial quedara oculto del mantenimiento, pero no se eliminara fisicamente." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay correos registrados.</td></tr>
                    @endforelse
                    @if ($emails->count() > 0)
                        <tr data-empty-filter style="display:none;">
                            <td colspan="7" class="text-center text-muted">No se encontraron correos con esos filtros.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
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
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('plantilla_id', $editEmail->plantilla_id ?? '') == $template->id)>{{ $template->nombre }}</option>
                                @endforeach
                            </select>
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
                        <label>Asunto</label>
                        <input type="text" name="asunto" class="form-control" value="{{ old('asunto', $editTemplate->asunto ?? '') }}" placeholder="Reporte de notas" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Cuerpo HTML</label>
                        <textarea name="cuerpo_html" rows="8" class="form-control" placeholder="<h1>Hola</h1><p>Contenido...</p>" required>{{ old('cuerpo_html', $editTemplate->cuerpo_html ?? '') }}</textarea>
                        <small class="text-muted">Puedes usar HTML basico para asunto y contenido del mensaje.</small>
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
