@extends('layouts.panel')

@section('title', 'Asistencia')

@section('content')
<style>
    .attendance-filters { display: grid; grid-template-columns: 1fr 2fr 1fr auto; gap: 1rem; align-items: end; }
    .attendance-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: .75rem; }
    .attendance-stat { border-radius: .8rem; padding: .8rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; }
    .attendance-stat strong { display: block; font-size: 1.35rem; line-height: 1.1; }
    .attendance-stat span { font-size: .78rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .attendance-stat.present strong { color: #16803c; }
    .attendance-stat.absent strong { color: #c12a3a; }
    .attendance-stat.justified strong { color: #a26300; }
    .attendance-mobile { display: none; }
    .attendance-deck { position: relative; max-width: 430px; height: 440px; margin: 0 auto; }
    .attendance-card { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid #dbe3ee; border-radius: 1.25rem; padding: 2rem; background: linear-gradient(145deg, #fff, #f8fafc); box-shadow: 0 18px 38px rgba(15, 23, 42, .12); touch-action: pan-y; user-select: none; transition: transform .22s ease, opacity .22s ease; }
    .attendance-card h3 { font-size: 1.8rem; font-weight: 700; color: #1f2937; }
    .attendance-card .attendance-index { color: #64748b; font-weight: 700; }
    .attendance-gesture { display: flex; justify-content: space-between; color: #64748b; font-size: .82rem; font-weight: 700; }
    .attendance-actions { display: grid; grid-template-columns: 1fr 1.15fr 1fr; gap: .6rem; }
    .attendance-actions .btn { min-height: 52px; border-radius: .8rem; font-weight: 700; }
    .swipe-no { color: #c12a3a; border-color: #f2c5cb; background: #fff7f8; }
    .swipe-yes { color: #16803c; border-color: #bde7c8; background: #f3fff6; }
    .swipe-justify { color: #8a5500; border-color: #f1d29d; background: #fffaf0; }
    .attendance-undo { display: block; margin: 1rem auto 0; }
    .attendance-empty { max-width: 460px; margin: 2rem auto; text-align: center; padding: 2rem; border: 1px dashed #cbd5e1; border-radius: 1rem; color: #64748b; }
    .attendance-table .custom-control { min-width: 86px; }
    .attendance-table .attendance-reason { min-width: 220px; }
    @media (max-width: 767.98px) {
        .attendance-filters { grid-template-columns: 1fr 1fr; }
        .attendance-filters .attendance-filter-submit { grid-column: span 2; }
        .attendance-stats { grid-template-columns: repeat(2, 1fr); }
        .attendance-desktop { display: none; }
        .attendance-mobile { display: block; }
    }
</style>

<div class="card maint-card">
    <div class="card-body">
        <form method="GET" action="{{ \App\Support\AppUrl::route('attendance.index') }}" class="attendance-filters">
            <div class="form-group mb-0">
                <label>Año lectivo</label>
                <select name="anio_escolar" class="form-control" onchange="this.form.submit()">
                    @foreach ($attendanceYears as $year)
                        <option value="{{ $year }}" @selected((int) $attendanceFilters['anio_escolar'] === (int) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label>Sección</label>
                <select name="seccion_id" class="form-control" onchange="this.form.submit()">
                    @forelse ($attendanceSections as $section)
                        <option value="{{ $section->id }}" @selected((int) $attendanceFilters['seccion_id'] === (int) $section->id)>{{ $section->grado }} {{ $section->nombre }}</option>
                    @empty
                        <option value="">Sin secciones disponibles</option>
                    @endforelse
                </select>
            </div>
            <div class="form-group mb-0">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ $attendanceFilters['fecha'] }}">
            </div>
            <button class="btn btn-primary attendance-filter-submit" style="background:#820005;border-color:#820005;"><i class="fas fa-search mr-1"></i> Ver asistencia</button>
        </form>
    </div>
</div>

@if ($attendanceFilters['seccion_id'])
    <div class="attendance-stats my-3" id="attendanceSummary">
        <div class="attendance-stat present"><strong data-summary="presente">{{ $attendanceSummary['presentes'] }}</strong><span>Presentes</span></div>
        <div class="attendance-stat absent"><strong data-summary="ausente">{{ $attendanceSummary['ausentes'] }}</strong><span>Ausentes</span></div>
        <div class="attendance-stat justified"><strong data-summary="justificado">{{ $attendanceSummary['justificados'] }}</strong><span>Justificados</span></div>
        <div class="attendance-stat"><strong data-summary="pendiente">{{ $attendanceSummary['pendientes'] }}</strong><span>Pendientes</span></div>
    </div>

    <div class="attendance-mobile">
        <div class="card maint-card">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div><strong>Control táctil</strong><div class="text-muted small">Desliza: izquierda no vino, derecha vino.</div></div>
                <i class="fas fa-hand-pointer text-muted"></i>
            </div>
            <div class="card-body pt-0">
                @if ($attendanceStudents->isNotEmpty())
                    <div class="attendance-deck" id="attendanceDeck">
                        @foreach ($attendanceStudents as $index => $student)
                            <article class="attendance-card" data-index="{{ $index }}" data-student-id="{{ $student['id'] }}" data-status="{{ $student['estado'] }}" data-reason="{{ $student['justificante'] }}">
                                <div>
                                    <div class="attendance-index">Alumno {{ $index + 1 }} de {{ $attendanceStudents->count() }}</div>
                                    <h3 class="mt-3">{{ $student['name'] }}</h3>
                                    <span class="badge badge-light border p-2 attendance-current-status">{{ $student['estado'] ? ucfirst($student['estado']) : 'Pendiente' }}</span>
                                </div>
                                <div>
                                    <div class="attendance-gesture mb-3"><span><i class="fas fa-arrow-left"></i> No vino</span><span>Vino <i class="fas fa-arrow-right"></i></span></div>
                                    <div class="attendance-actions">
                                        <button type="button" class="btn swipe-no" data-mark="ausente"><i class="fas fa-times d-block mb-1"></i>No vino</button>
                                        <button type="button" class="btn swipe-justify" data-justify><i class="fas fa-file-medical d-block mb-1"></i>Justificar</button>
                                        <button type="button" class="btn swipe-yes" data-mark="presente"><i class="fas fa-check d-block mb-1"></i>Vino</button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm attendance-undo" id="attendanceUndo" disabled><i class="fas fa-undo mr-1"></i>Deshacer última acción</button>
                @else
                    <div class="attendance-empty">No hay alumnos activos en esta sección.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="attendance-desktop card maint-card">
        <div class="card-header border-0">
            <strong>Listado de asistencia</strong>
            <span class="text-muted small ml-2">Marca presencia o ausencia y guarda cambios.</span>
        </div>
        <form method="POST" action="{{ \App\Support\AppUrl::route('attendance.sync') }}">
            @csrf
            <input type="hidden" name="seccion_id" value="{{ $attendanceFilters['seccion_id'] }}">
            <input type="hidden" name="fecha" value="{{ $attendanceFilters['fecha'] }}">
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table maint-table attendance-table">
                        <thead><tr><th>#</th><th>Alumno</th><th>Vino</th><th>No vino</th><th>Justificante</th><th>Motivo</th></tr></thead>
                        <tbody>
                        @forelse ($attendanceStudents as $index => $student)
                            @php($isJustified = $student['estado'] === 'justificado')
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $student['name'] }}</strong></td>
                                <td><div class="custom-control custom-radio"><input class="custom-control-input" type="radio" id="present-{{ $student['id'] }}" name="estado[{{ $student['id'] }}]" value="presente" @checked($student['estado'] === 'presente')><label class="custom-control-label text-success" for="present-{{ $student['id'] }}">Sí</label></div></td>
                                <td><div class="custom-control custom-radio"><input class="custom-control-input" type="radio" id="absent-{{ $student['id'] }}" name="estado[{{ $student['id'] }}]" value="ausente" @checked(in_array($student['estado'], ['ausente', 'justificado'], true))><label class="custom-control-label text-danger" for="absent-{{ $student['id'] }}">No</label></div></td>
                                <td><select class="form-control form-control-sm attendance-justification" name="tipo_justificante[{{ $student['id'] }}]" data-student="{{ $student['id'] }}"><option value="sin_justificante" @selected(! $isJustified)>Sin justificante</option><option value="justificante" @selected($isJustified)>Justificante</option></select></td>
                                <td><input type="text" class="form-control form-control-sm attendance-reason" name="motivo[{{ $student['id'] }}]" data-student="{{ $student['id'] }}" value="{{ $student['justificante'] }}" placeholder="Motivo de ausencia" @disabled(! $isJustified)></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No hay alumnos activos en esta sección.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($attendanceStudents->isNotEmpty())
                <div class="card-footer bg-white border-0 text-right"><button class="btn btn-success"><i class="fas fa-save mr-1"></i>Guardar asistencia</button></div>
            @endif
        </form>
    </div>

    <div class="modal fade" id="attendanceJustificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Justificar ausencia</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body"><p class="text-muted small mb-2" id="justificationStudentName"></p><label>Motivo</label><textarea id="attendanceJustificationReason" class="form-control" rows="4" maxlength="1000" placeholder="Ejemplo: cita médica, situación familiar..."></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button><button type="button" class="btn btn-warning" id="saveAttendanceJustification">Guardar justificante</button></div>
        </div></div>
    </div>
@else
    <div class="attendance-empty">Selecciona una sección para iniciar control de asistencia.</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.attendance-justification').forEach(function (select) {
        select.addEventListener('change', function () {
            const input = document.querySelector('.attendance-reason[data-student="' + select.dataset.student + '"]');
            const enabled = select.value === 'justificante';
            input.disabled = !enabled;
            if (!enabled) input.value = '';
        });
    });

    const deck = document.getElementById('attendanceDeck');
    if (!deck) return;

    const cards = Array.from(deck.querySelectorAll('.attendance-card'));
    const undoButton = document.getElementById('attendanceUndo');
    const csrf = @json(csrf_token());
    const sectionId = @json((int) $attendanceFilters['seccion_id']);
    const date = @json($attendanceFilters['fecha']);
    const baseUrl = @json(app_nav_url('asistencia'));
    const history = [];
    let currentIndex = Math.max(0, cards.findIndex(function (card) { return !card.dataset.status; }));
    if (currentIndex < 0) currentIndex = 0;
    let justificationCard = null;
    let startX = 0;
    let dragging = false;

    function label(status) { return status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Pendiente'; }
    function renderDeck() {
        cards.forEach(function (card, index) {
            const distance = index - currentIndex;
            card.style.display = distance < 0 ? 'none' : 'flex';
            card.style.transform = distance === 0 ? 'translateX(0)' : 'translateY(' + Math.min(distance * 9, 25) + 'px) scale(' + (1 - Math.min(distance * .025, .08)) + ')';
            card.style.opacity = distance > 2 ? '0' : String(1 - Math.min(distance * .14, .4));
            card.style.zIndex = String(100 - index);
        });
    }
    function refreshSummary() {
        const counts = { presente: 0, ausente: 0, justificado: 0, pendiente: 0 };
        cards.forEach(function (card) { counts[card.dataset.status || 'pendiente']++; });
        Object.keys(counts).forEach(function (key) { const target = document.querySelector('[data-summary="' + key + '"]'); if (target) target.textContent = counts[key]; });
    }
    function request(url, data) {
        return fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(data) })
            .then(function (response) { if (!response.ok) return response.json().then(function (body) { throw new Error(body.message || 'No se pudo guardar la asistencia.'); }); return response.json(); });
    }
    function saveCard(card, status, reason) {
        const previous = { status: card.dataset.status || '', reason: card.dataset.reason || '', index: Number(card.dataset.index) };
        const studentId = card.dataset.studentId;
        request(baseUrl + '/' + studentId, { seccion_id: sectionId, fecha: date, estado: status, justificante: reason || null })
            .then(function (result) {
                history.push({ studentId: studentId, previous: previous });
                card.dataset.status = result.estado;
                card.dataset.reason = result.justificante || '';
                card.querySelector('.attendance-current-status').textContent = label(result.estado);
                currentIndex = Math.min(currentIndex + 1, cards.length);
                undoButton.disabled = false;
                refreshSummary();
                renderDeck();
            })
            .catch(function (error) { Swal.fire({ icon: 'error', title: 'No se guardó', text: error.message, confirmButtonColor: '#820005' }); renderDeck(); });
    }
    function swipe(card, direction) {
        card.style.transform = 'translateX(' + (direction > 0 ? 520 : -520) + 'px) rotate(' + (direction * 9) + 'deg)';
        setTimeout(function () { saveCard(card, direction > 0 ? 'presente' : 'ausente', ''); }, 150);
    }
    deck.addEventListener('pointerdown', function (event) { const card = event.target.closest('.attendance-card'); if (!card || Number(card.dataset.index) !== currentIndex) return; dragging = true; startX = event.clientX; card.setPointerCapture?.(event.pointerId); });
    deck.addEventListener('pointermove', function (event) { const card = event.target.closest('.attendance-card'); if (!dragging || !card) return; const offset = event.clientX - startX; card.style.transform = 'translateX(' + offset + 'px) rotate(' + (offset / 20) + 'deg)'; });
    deck.addEventListener('pointerup', function (event) { const card = event.target.closest('.attendance-card'); if (!dragging || !card) return; dragging = false; const offset = event.clientX - startX; if (Math.abs(offset) > 90) swipe(card, offset > 0 ? 1 : -1); else renderDeck(); });
    deck.addEventListener('click', function (event) {
        const card = event.target.closest('.attendance-card');
        if (!card || Number(card.dataset.index) !== currentIndex) return;
        const marker = event.target.closest('[data-mark]');
        if (marker) { swipe(card, marker.dataset.mark === 'presente' ? 1 : -1); return; }
        if (event.target.closest('[data-justify]')) { justificationCard = card; document.getElementById('justificationStudentName').textContent = card.querySelector('h3').textContent; document.getElementById('attendanceJustificationReason').value = card.dataset.reason || ''; $('#attendanceJustificationModal').modal('show'); }
    });
    document.getElementById('saveAttendanceJustification')?.addEventListener('click', function () { const reason = document.getElementById('attendanceJustificationReason').value.trim(); if (!reason) { Swal.fire({ icon: 'warning', title: 'Motivo requerido', text: 'Ingresa motivo del justificante.', confirmButtonColor: '#820005' }); return; } $('#attendanceJustificationModal').modal('hide'); saveCard(justificationCard, 'justificado', reason); });
    undoButton.addEventListener('click', function () { const last = history.pop(); if (!last) return; const card = cards.find(function (item) { return item.dataset.studentId === last.studentId; }); request(baseUrl + '/' + last.studentId + '/restaurar', { seccion_id: sectionId, fecha: date, estado_anterior: last.previous.status || null, justificante_anterior: last.previous.reason || null }).then(function () { card.dataset.status = last.previous.status; card.dataset.reason = last.previous.reason; card.querySelector('.attendance-current-status').textContent = label(last.previous.status); currentIndex = last.previous.index; undoButton.disabled = history.length === 0; refreshSummary(); renderDeck(); }).catch(function (error) { history.push(last); Swal.fire({ icon: 'error', title: 'No se pudo deshacer', text: error.message, confirmButtonColor: '#820005' }); }); });
    renderDeck();
});
</script>
@endpush
