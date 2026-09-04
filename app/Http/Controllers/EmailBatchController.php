<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailDispatchJob;
use App\Models\EmailBatch;
use App\Models\EmailDispatch;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailBatchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plantilla_id' => ['required', 'integer', 'exists:plantillas_correo,id'],
            'seccion_id' => ['required', 'integer', 'exists:secciones,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'reenviar' => ['nullable', 'boolean'],
        ]);

        $template = EmailTemplate::with('roles')->findOrFail($data['plantilla_id']);
        $this->guardTemplateAccess($template);

        $relations = $this->recipients((int) $data['seccion_id']);
        $alreadySent = $this->alreadySent($data, $relations, $request->boolean('reenviar'));
        $sendable = $relations->reject(fn ($recipient) => $alreadySent->contains($recipient->padre_id.'-'.$recipient->alumno_id));
        $omitted = $relations->count() - $sendable->count();

        if ($sendable->isEmpty()) {
            return redirect(AppUrl::route('emails.index'))->with('error', 'No hay destinatarios nuevos con correo valido para esta seccion y trimestre.');
        }

        $batch = DB::transaction(function () use ($data, $template, $sendable, $omitted): EmailBatch {
            $batch = EmailBatch::query()->create([
                'usuario_id' => Auth::id(),
                'plantilla_id' => $template->id,
                'seccion_id' => $data['seccion_id'],
                'trimestre_id' => $data['trimestre_id'],
                'nombre' => 'Lote '.$template->nombre.' - '.now()->format('d/m/Y H:i'),
                'estado' => 'procesando',
                'total' => $sendable->count(),
                'omitidos' => $omitted,
                'iniciado_en' => now(),
            ]);

            foreach ($sendable as $recipient) {
                $dispatch = EmailDispatch::query()->create([
                    'lote_id' => $batch->id,
                    'usuario_id' => Auth::id(),
                    'plantilla_id' => $template->id,
                    'padre_id' => $recipient->padre_id,
                    'alumno_id' => $recipient->alumno_id,
                    'trimestre_id' => $data['trimestre_id'],
                    'estado' => 'pendiente',
                    'en_cola' => true,
                    'destinatario_email' => $recipient->email_principal,
                    'adjuntos_generados' => $template->documentos_generados ?? [],
                    'activo' => true,
                ]);

                SendEmailDispatchJob::dispatch($dispatch->id)->onConnection('database')->onQueue('emails');
            }

            return $batch;
        });

        return redirect(AppUrl::route('emails.index'))->with('status', 'Lote #'.$batch->id.' creado con '.$batch->total.' correos en cola'.($batch->omitidos ? ' y '.$batch->omitidos.' omitidos por envio previo.' : '.'));
    }

    public function retryFailed(EmailBatch $batch): RedirectResponse
    {
        $batch->load('template.roles');
        $this->guardTemplateAccess($batch->template);
        $failedDispatches = $batch->dispatches()->where('activo', true)->where('estado', 'fallido')->get();

        if ($failedDispatches->isEmpty()) {
            return redirect(AppUrl::route('emails.index'))->with('error', 'Este lote no tiene correos fallidos para reintentar.');
        }

        DB::transaction(function () use ($batch, $failedDispatches): void {
            $batch->update([
                'estado' => 'procesando',
                'procesados' => max(0, $batch->procesados - $batch->fallidos),
                'fallidos' => 0,
                'finalizado_en' => null,
            ]);

            foreach ($failedDispatches as $dispatch) {
                $dispatch->update([
                    'estado' => 'pendiente',
                    'en_cola' => true,
                    'usuario_id' => Auth::id(),
                    'error_mensaje' => null,
                ]);

                SendEmailDispatchJob::dispatch($dispatch->id)->onConnection('database')->onQueue('emails');
            }
        });

        return redirect(AppUrl::route('emails.index'))->with('status', $failedDispatches->count().' correos fallidos fueron enviados nuevamente a la cola.');
    }

    public function export(EmailBatch $batch): StreamedResponse
    {
        $batch->load('template.roles');
        $this->guardTemplateAccess($batch->template);
        $rows = DB::table('envios_correo as ec')
            ->join('padres as p', 'p.id', '=', 'ec.padre_id')
            ->join('alumnos as a', 'a.id', '=', 'ec.alumno_id')
            ->where('ec.lote_id', $batch->id)
            ->selectRaw("CONCAT(p.nombres, ' ', p.apellidos) as familiar, p.email_principal, CONCAT(a.nombres, ' ', a.apellidos) as alumno, ec.destinatario_email, ec.estado, ec.error_mensaje, ec.enviado_en")
            ->orderBy('ec.id')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['Familiar', 'Correo registrado', 'Alumno', 'Correo destinatario', 'Estado', 'Error', 'Enviado en']);
            foreach ($rows as $row) fputcsv($output, [$row->familiar, $row->email_principal, $row->alumno, $row->destinatario_email, $row->estado, $row->error_mensaje, $row->enviado_en]);
            fclose($output);
        }, 'lote-correos-'.$batch->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function recipients(int $sectionId)
    {
        return DB::table('padre_alumno as pa')
            ->join('padres as p', 'p.id', '=', 'pa.padre_id')
            ->join('alumnos as a', 'a.id', '=', 'pa.alumno_id')
            ->where('a.seccion_id', $sectionId)
            ->where('a.activo', true)
            ->where('p.activo', true)
            ->whereNotNull('p.email_principal')
            ->where('p.email_principal', '<>', '')
            ->select('pa.padre_id', 'pa.alumno_id', 'p.email_principal')
            ->distinct()
            ->get();
    }

    private function alreadySent(array $data, $relations, bool $reenviar)
    {
        if ($reenviar) {
            return collect();
        }

        return DB::table('envios_correo')
            ->where('plantilla_id', $data['plantilla_id'])
            ->where('trimestre_id', $data['trimestre_id'])
            ->where('estado', 'enviado')
            ->whereIn('padre_id', $relations->pluck('padre_id')->unique())
            ->whereIn('alumno_id', $relations->pluck('alumno_id')->unique())
            ->selectRaw("CONCAT(padre_id, '-', alumno_id) as relation_key")
            ->pluck('relation_key');
    }

    private function guardTemplateAccess(EmailTemplate $template): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('role');

        if (($user->role->nombre ?? null) === 'admin') {
            return;
        }

        $allowedRoleIds = $template->roles()->pluck('roles.id');
        abort_unless($allowedRoleIds->isEmpty() || $allowedRoleIds->contains((int) $user->rol_id), 403);
    }
}
