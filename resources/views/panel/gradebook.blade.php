@extends('layouts.panel')

@section('title', 'Notas')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card sticky-card">
            <div class="card-header"><h3 class="card-title">{{ $editNote ? 'Editar nota' : 'Nueva nota' }}</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ $editNote ? '/pad/notas/'.$editNote->id : '/pad/notas' }}">
                    @csrf
                    @if ($editNote) @method('PATCH') @endif
                    <div class="form-group">
                        <label>Alumno</label>
                        <select name="alumno_id" class="form-control" required>
                            <option value="">Seleccione un alumno</option>
                            @foreach ($studentsForNotes as $student)
                                <option value="{{ $student->id }}" @selected(old('alumno_id', $editNote->alumno_id ?? '') == $student->id)>{{ $student->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Asignacion</label>
                        <select name="asignacion_id" class="form-control" required>
                            <option value="">Seleccione una asignacion</option>
                            @foreach ($assignmentOptions as $assignment)
                                <option value="{{ $assignment->id }}" @selected(old('asignacion_id', $editNote->asignacion_id ?? request('assignment_id')) == $assignment->id)>{{ $assignment->etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trimestre</label>
                        <select name="trimestre_id" class="form-control" required>
                            @foreach ($trimesters as $trimester)
                                <option value="{{ $trimester->id }}" @selected(old('trimestre_id', $editNote->trimestre_id ?? $gradeBoard['selected_trimestre_id']) == $trimester->id)>{{ $trimester->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria_id" class="form-control" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('categoria_id', $editNote->categoria_id ?? '') == $category->id)>{{ $category->nombre }} ({{ rtrim(rtrim(number_format($category->porcentaje, 2), '0'), '.') }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valor</label>
                        <input type="number" step="0.01" min="0" max="100" name="valor" class="form-control" value="{{ old('valor', $editNote->valor ?? '') }}" required>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ $editNote ? 'Guardar cambios' : 'Agregar nota' }}</button>
                    @if ($editNote)<a href="/pad/notas" class="btn btn-default btn-sm">Cancelar</a>@endif
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notas registradas</h3>
                <div class="card-tools w-100 mt-2 mt-md-0">
                    <div class="filter-toolbar justify-content-md-end" data-filter-target="notes-table">
                        <div class="form-group">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Alumno, asignacion o categoria" data-filter-name="text">
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
                        <div class="form-group">
                            <label class="small text-muted mb-1">Categoria</label>
                            <select class="form-control form-control-sm" data-filter-name="category">
                                <option value="">Todas</option>
                                @foreach ($categories as $category)
                                    <option value="{{ strtolower($category->nombre) }}">{{ $category->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover" id="notes-table">
                    <thead class="bg-light"><tr><th>#</th><th>Alumno</th><th>Asignacion</th><th>Trimestre</th><th>Categoria</th><th>Valor</th><th>Acciones</th></tr></thead>
                    <tbody>
                    @forelse ($noteRows as $note)
                        <tr data-filter-row data-text="{{ strtolower($note->alumno.' '.$note->asignacion.' '.$note->categoria.' '.$note->trimestre.' '.$note->valor) }}" data-trimester="{{ strtolower($note->trimestre) }}" data-category="{{ strtolower($note->categoria) }}">
                            <td>{{ $note->id }}</td>
                            <td><strong>{{ $note->alumno }}</strong></td>
                            <td>{{ $note->asignacion }}</td>
                            <td>{{ $note->trimestre }}</td>
                            <td>{{ $note->categoria }}</td>
                            <td><span class="score-badge {{ $note->valor >= 85 ? 'score-high' : ($note->valor >= 70 ? 'score-mid' : 'score-low') }}">{{ $note->valor }}</span></td>
                            <td class="action-cell">
                                <a href="/pad/notas?edit_note={{ $note->id }}" class="btn btn-xs btn-warning">Editar</a>
                                <form method="POST" action="{{ '/pad/notas/'.$note->id }}" data-swal-confirm="true" data-swal-title="Desactivar nota" data-swal-text="La nota dejara de mostrarse en el mantenimiento, pero se conservara en la base de datos." data-swal-confirm-label="Si, desactivar">@csrf @method('DELETE')<button class="btn btn-xs btn-danger">Desactivar</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay notas registradas.</td></tr>
                    @endforelse
                    @if ($noteRows->count() > 0)
                        <tr data-empty-filter style="display:none;">
                            <td colspan="7" class="text-center text-muted">No se encontraron notas con esos filtros.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tablero consolidado</h3>
                @if ($gradeBoard['assignment'])
                    <div class="card-tools text-muted small">{{ $gradeBoard['assignment']->materia }} | {{ $gradeBoard['assignment']->grado }} {{ $gradeBoard['assignment']->seccion }} | {{ $gradeBoard['assignment']->profesor }}</div>
                @endif
            </div>
            <div class="card-body">
                <form method="GET" action="/pad/notas" class="row mb-3">
                    <div class="col-md-7">
                        <label>Asignacion</label>
                        <select name="assignment_id" class="form-control">
                            @foreach ($assignmentOptions as $assignment)
                                <option value="{{ $assignment->id }}" @selected(request('assignment_id', $gradeBoard['assignment']->id ?? '') == $assignment->id)>{{ $assignment->etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Trimestre</label>
                        <select name="trimestre_id" class="form-control">
                            @foreach ($trimesters as $trimester)
                                <option value="{{ $trimester->id }}" @selected($gradeBoard['selected_trimestre_id'] == $trimester->id)>{{ $trimester->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-outline-primary btn-block">Ver</button>
                    </div>
                </form>

                <div class="mb-3">
                    @foreach ($gradeBoard['categories'] as $category)
                        <span class="weight-pill">{{ $category->nombre }} {{ rtrim(rtrim(number_format($category->porcentaje, 2), '0'), '.') }}%</span>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light"><tr><th>#</th><th>Alumno</th>@foreach ($gradeBoard['categories'] as $category)<th class="text-center">{{ $category->nombre }}</th>@endforeach<th class="text-center">Final</th></tr></thead>
                        <tbody>
                        @forelse ($gradeBoard['rows'] as $row)
                            <tr>
                                <td>{{ $row['id'] }}</td>
                                <td>{{ $row['nombre'] }}</td>
                                @foreach ($gradeBoard['categories'] as $category)
                                    <td class="text-center">{{ $row['grades'][$category->id] ?? '-' }}</td>
                                @endforeach
                                <td class="final-cell">{{ $row['final'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 3 + $gradeBoard['categories']->count() }}" class="text-center text-muted">No hay tablero de notas disponible.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
