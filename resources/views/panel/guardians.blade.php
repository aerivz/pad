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
@endphp

<div class="row">
    <div class="col-lg-5">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editGuardian ? 'Editar miembro familiar' : 'Nuevo miembro familiar' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editGuardian ? '/pad/familias/'.$editGuardian->id : '/pad/familias' }}">
                    @csrf
                    @if ($editGuardian) @method('PATCH') @endif
                    <div class="form-group"><label>Nombres</label><input name="nombres" class="form-control" value="{{ old('nombres', $editGuardian->nombres ?? '') }}" required></div>
                    <div class="form-group"><label>Apellidos</label><input name="apellidos" class="form-control" value="{{ old('apellidos', $editGuardian->apellidos ?? '') }}" required></div>
                    <div class="form-group"><label>Correo principal</label><input type="email" name="email_principal" class="form-control" value="{{ old('email_principal', $editGuardian->email_principal ?? '') }}" required></div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Vinculos familiares</label>
                        <button type="button" class="btn btn-xs btn-outline-primary" id="add-family-link">Agregar vinculo</button>
                    </div>

                    <div id="family-links">
                        @foreach ($familyMembers as $index => $member)
                            <div class="border rounded p-2 mb-2 family-link-row">
                                <div class="form-group mb-2">
                                    <label>Seccion</label>
                                    <select class="form-control family-section-select" data-name="seccion_id">
                                        <option value="">Todas</option>
                                        @foreach ($studentSections as $section)
                                            <option value="{{ $section->id }}" @selected((string) ($member['seccion_id'] ?? '') === (string) $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Alumno</label>
                                    <select name="members[{{ $index }}][alumno_id]" class="form-control family-student-select" data-name="alumno_id" required>
                                        <option value="">Seleccione un alumno</option>
                                        @foreach ($studentsForFamily as $student)
                                            <option value="{{ $student->id }}" data-section-id="{{ $student->seccion_id }}" @selected((string) ($member['alumno_id'] ?? '') === (string) $student->id)>{{ $student->nombre_completo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label>Parentesco</label>
                                    <select name="members[{{ $index }}][parentesco]" class="form-control" required>
                                        @foreach ($relationshipOptions as $option)
                                            <option value="{{ $option }}" @selected(($member['parentesco'] ?? 'Padre') === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-danger remove-family-link">Quitar</button>
                            </div>
                        @endforeach
                    </div>

                    <button class="btn btn-primary btn-sm">{{ $editGuardian ? 'Guardar cambios' : 'Agregar familiar' }}</button>
                    @if ($editGuardian)<a href="/pad/familias" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Miembros familiares registrados</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead class="bg-light"><tr><th>#</th><th>Familiar</th><th>Correo</th><th>Alumnos asociados</th><th>Vinculos</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @foreach ($parents as $parent)
                        <tr>
                            <td>{{ $parent->id }}</td>
                            <td><strong>{{ $parent->nombres }} {{ $parent->apellidos }}</strong></td>
                            <td>{{ $parent->email_principal }}</td>
                            <td>{{ $parent->total_hijos }}</td>
                            <td class="small">{{ $parent->miembros ?: 'Sin vinculos' }}</td>
                            <td>{!! $parent->ultimo_envio_id ? '<span class="badge badge-success">Con historial</span>' : '<span class="badge badge-secondary">Sin envios</span>' !!}</td>
                            <td class="action-cell">
                                <a href="/pad/familias?edit_guardian={{ $parent->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/familias/'.$parent->id }}" data-swal-confirm="true" data-swal-title="Desactivar familiar" data-swal-text="El miembro familiar quedara inactivo y dejara de mostrarse en la gestion principal." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<template id="family-link-template">
    <div class="border rounded p-2 mb-2 family-link-row">
        <div class="form-group mb-2">
            <label>Seccion</label>
            <select class="form-control family-section-select" data-name="seccion_id">
                <option value="">Todas</option>
                @foreach ($studentSections as $section)
                    <option value="{{ $section->id }}">{{ $section->grado }} {{ $section->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-2">
            <label>Alumno</label>
            <select class="form-control family-student-select" data-name="alumno_id" required>
                <option value="">Seleccione un alumno</option>
                @foreach ($studentsForFamily as $student)
                    <option value="{{ $student->id }}" data-section-id="{{ $student->seccion_id }}">{{ $student->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group mb-2">
            <label>Parentesco</label>
            <select class="form-control family-relationship-select" data-name="parentesco" required>
                @foreach ($relationshipOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-xs btn-outline-danger remove-family-link">Quitar</button>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const linksContainer = document.getElementById('family-links');
        const addButton = document.getElementById('add-family-link');
        const template = document.getElementById('family-link-template');

        if (!linksContainer || !addButton || !template) {
            return;
        }

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

        const syncAllRows = function () {
            linksContainer.querySelectorAll('.family-link-row').forEach(syncRowStudents);
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
        syncAllRows();
    });
</script>
@endsection
