@extends('layouts.panel')

@section('title', 'Familias')

@section('content')
@php
    $studentSectionLookup = collect($studentsForFamily)->pluck('seccion_id', 'id');
    $familyMembers = collect(old('members', $editGuardian?->students->map(fn ($student) => [
        'alumno_id' => $student->id,
        'parentesco' => $student->pivot->parentesco,
    ])->values()->all() ?? [['alumno_id' => '', 'parentesco' => 'Padre']]))
        ->map(function ($member) use ($studentSectionLookup) {
            $member['seccion_id'] = $member['seccion_id'] ?? ($studentSectionLookup[(int) ($member['alumno_id'] ?? 0)] ?? '');
            return $member;
        })
        ->all();
    $formVisible = $editGuardian !== null || $errors->any();
@endphp

<div class="card maint-card">
    <div class="card-header border-0">
        <div class="maint-toolbar">
            <div class="maint-toolbar-title">
                <i class="fas fa-users"></i>
                <span>Miembros familiares</span>
            </div>
            <div class="maint-actions">
                <button class="btn btn-success btn-sm" type="button" data-toggle="modal" data-target="#guardianFormModal">
                    <i class="fas fa-plus mr-1"></i>{{ $editGuardian ? 'Editar familiar' : 'Nuevo familiar' }}
                </button>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <div class="maint-search-grid mb-3" data-filter-target="guardians-table">
            <div class="form-group">
                <label>Busqueda</label>
                <input type="text" class="form-control" placeholder="Nombre, correo o vinculo" data-filter-name="text">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select class="form-control" data-filter-name="status">
                    <option value="">Todos</option>
                    <option value="con historial">Con historial</option>
                    <option value="sin envios">Sin envios</option>
                </select>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <button type="button" class="btn btn-primary" data-filter-submit><i class="fas fa-search mr-1"></i>Buscar</button>
                <button type="button" class="btn btn-default" data-filter-reset><i class="fas fa-eraser mr-1"></i>Limpiar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover maint-table" id="guardians-table">
                <thead class="bg-light"><tr><th>#</th><th>Familiar</th><th>Correo</th><th>Alumnos</th><th>Vinculos</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                @forelse ($parents as $parent)
                    @php($historyLabel = $parent->ultimo_envio_id ? 'con historial' : 'sin envios')
                    <tr data-filter-row data-text="{{ strtolower($parent->nombres.' '.$parent->apellidos.' '.$parent->email_principal.' '.$parent->miembros) }}" data-status="{{ $historyLabel }}">
                        <td>{{ $parent->id }}</td>
                        <td>
                            <div class="maint-identity">
                                <span class="maint-avatar">{{ strtoupper(substr($parent->nombres, 0, 1).substr($parent->apellidos, 0, 1)) }}</span>
                                <div>
                                    <strong>{{ $parent->nombres }} {{ $parent->apellidos }}</strong>
                                    <div class="small text-muted">{{ $parent->total_hijos }} alumnos asociados</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $parent->email_principal }}</td>
                        <td>{{ $parent->total_hijos }}</td>
                        <td class="small">{{ $parent->miembros ?: 'Sin vinculos' }}</td>
                        <td>
                            @if ($parent->ultimo_envio_id)
                                <span class="maint-status maint-status-active">Con historial</span>
                            @else
                                <span class="maint-status maint-status-muted">Sin envios</span>
                            @endif
                        </td>
                        <td class="maint-actions-cell">
                            <button type="button" class="btn btn-xs btn-info guardian-view-button" data-name="{{ $parent->nombres }} {{ $parent->apellidos }}" data-email="{{ $parent->email_principal }}" data-students="{{ $parent->total_hijos }}" data-links="{{ $parent->miembros ?: 'Sin vinculos' }}" data-status="{{ $parent->ultimo_envio_id ? 'Con historial' : 'Sin envios' }}"><i class="fas fa-eye"></i></button>
                            <a href="{{ \App\Support\AppUrl::route('guardians.index') }}?edit_guardian={{ $parent->id }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                            <form method="POST" action="{{ \App\Support\AppUrl::route('guardians.destroy', ['guardian' => $parent->id]) }}" data-swal-confirm="true" data-swal-title="Desactivar familiar" data-swal-text="El miembro familiar quedara inactivo y dejara de mostrarse en la gestion principal." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger"><i class="fas fa-user-slash"></i></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">No hay familiares registrados.</td></tr>
                @endforelse
                @if ($parents->count() > 0)
                    <tr data-empty-filter style="display:none;"><td colspan="7" class="text-center text-muted">No se encontraron familiares con esos filtros.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="guardianFormModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <h5 class="modal-title mb-0">{{ $editGuardian ? 'Editar miembro familiar' : 'Nuevo miembro familiar' }}</h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-xs btn-outline-primary mr-2" id="add-family-link">Agregar vinculo</button>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ $editGuardian ? \App\Support\AppUrl::route('guardians.update', ['guardian' => $editGuardian->id]) : \App\Support\AppUrl::route('guardians.store') }}">
                    @csrf
                    @if ($editGuardian) @method('PATCH') @endif
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editGuardian->nombres ?? '') }}" required></div>
                        <div class="col-md-4 form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editGuardian->apellidos ?? '') }}" required></div>
                        <div class="col-md-4 form-group"><label>Correo principal</label><input type="email" name="email_principal" class="form-control" value="{{ old('email_principal', $editGuardian->email_principal ?? '') }}" required></div>
                    </div>
                    <div id="family-links">
                        @foreach ($familyMembers as $index => $member)
                            <div class="border rounded p-3 mb-2 family-link-row">
                                <div class="row">
                                    <div class="col-md-4 form-group mb-2">
                                        <label>Seccion</label>
                                        <select class="form-control family-section-select" data-name="seccion_id">
                                            <option value="">Todas</option>
                                            @foreach ($studentSections as $section)
                                                <option value="{{ $section->id }}" @selected((string) ($member['seccion_id'] ?? '') === (string) $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label>Alumno</label>
                                        <select name="members[{{ $index }}][alumno_id]" class="form-control family-student-select" data-name="alumno_id" required>
                                            <option value="">Seleccione un alumno</option>
                                            @foreach ($studentsForFamily as $student)
                                                <option value="{{ $student->id }}" data-section-id="{{ $student->seccion_id }}" @selected((string) ($member['alumno_id'] ?? '') === (string) $student->id)>{{ $student->nombre_completo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label>Parentesco</label>
                                        <select name="members[{{ $index }}][parentesco]" class="form-control" required>
                                            @foreach ($relationshipOptions as $option)
                                                <option value="{{ $option }}" @selected(($member['parentesco'] ?? 'Padre') === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end mb-2">
                                        <button type="button" class="btn btn-xs btn-outline-danger remove-family-link w-100">Quitar</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editGuardian ? 'Guardar cambios' : 'Agregar familiar' }}</button>
                    @if ($editGuardian)<a href="{{ \App\Support\AppUrl::route('guardians.index') }}" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
</div>

<template id="family-link-template">
    <div class="border rounded p-3 mb-2 family-link-row">
        <div class="row">
            <div class="col-md-4 form-group mb-2">
                <label>Seccion</label>
                <select class="form-control family-section-select" data-name="seccion_id">
                    <option value="">Todas</option>
                    @foreach ($studentSections as $section)
                        <option value="{{ $section->id }}">{{ $section->grado }} {{ $section->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 form-group mb-2">
                <label>Alumno</label>
                <select class="form-control family-student-select" data-name="alumno_id" required>
                    <option value="">Seleccione un alumno</option>
                    @foreach ($studentsForFamily as $student)
                        <option value="{{ $student->id }}" data-section-id="{{ $student->seccion_id }}">{{ $student->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group mb-2">
                <label>Parentesco</label>
                <select class="form-control family-relationship-select" data-name="parentesco" required>
                    @foreach ($relationshipOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end mb-2">
                <button type="button" class="btn btn-xs btn-outline-danger remove-family-link w-100">Quitar</button>
            </div>
        </div>
    </div>
</template>

<div class="modal fade" id="guardianViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Detalle de familiar</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <ul class="maint-modal-list">
                    <li><strong>Nombre:</strong> <span data-guardian-field="name"></span></li>
                    <li><strong>Correo:</strong> <span data-guardian-field="email"></span></li>
                    <li><strong>Alumnos:</strong> <span data-guardian-field="students"></span></li>
                    <li><strong>Vinculos:</strong> <span data-guardian-field="links"></span></li>
                    <li><strong>Estado:</strong> <span data-guardian-field="status"></span></li>
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
        $('#guardianFormModal').modal('show');
        @endif

        const linksContainer = document.getElementById('family-links');
        const addButton = document.getElementById('add-family-link');
        const template = document.getElementById('family-link-template');

        if (linksContainer && addButton && template) {
            const refreshIndexes = function () {
                linksContainer.querySelectorAll('.family-link-row').forEach(function (row, index) {
                    row.querySelectorAll('[data-name]').forEach(function (field) {
                        if (field.dataset.name === 'seccion_id') {
                            field.removeAttribute('name');
                            return;
                        }

                        field.name = 'members[' + index + '][' + field.dataset.name + ']';
                    });
                });
            };

            const syncRowStudents = function (row) {
                const sectionSelect = row.querySelector('.family-section-select');
                const studentSelect = row.querySelector('.family-student-select');

                if (!sectionSelect || !studentSelect) {
                    return;
                }

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

            addButton.addEventListener('click', function () {
                const clone = template.content.firstElementChild.cloneNode(true);
                linksContainer.appendChild(clone);
                refreshIndexes();
                syncRowStudents(clone);
            });

            linksContainer.addEventListener('change', function (event) {
                if (event.target.classList.contains('family-section-select')) {
                    syncRowStudents(event.target.closest('.family-link-row'));
                }
            });

            linksContainer.addEventListener('click', function (event) {
                if (!event.target.classList.contains('remove-family-link')) {
                    return;
                }

                const rows = linksContainer.querySelectorAll('.family-link-row');
                if (rows.length === 1) {
                    return;
                }

                event.target.closest('.family-link-row').remove();
                refreshIndexes();
            });

            refreshIndexes();
            linksContainer.querySelectorAll('.family-link-row').forEach(syncRowStudents);
        }

        document.querySelectorAll('.guardian-view-button').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelector('[data-guardian-field="name"]').textContent = button.dataset.name;
                document.querySelector('[data-guardian-field="email"]').textContent = button.dataset.email;
                document.querySelector('[data-guardian-field="students"]').textContent = button.dataset.students;
                document.querySelector('[data-guardian-field="links"]').textContent = button.dataset.links;
                document.querySelector('[data-guardian-field="status"]').textContent = button.dataset.status;
                $('#guardianViewModal').modal('show');
            });
        });
    });
</script>
@endpush
