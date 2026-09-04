<?php

namespace App\Jobs;

use App\Models\EmailBatch;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifyEmailBatchCompletedJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(public int $batchId)
    {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        $batch = EmailBatch::query()->with(['template', 'section', 'user'])->find($this->batchId);

        if (! $batch || ! in_array($batch->estado, ['completado', 'completado_con_errores'], true)) {
            return;
        }

        $recipients = User::query()
            ->where('activo', true)
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->where(function ($query) use ($batch): void {
                $query->where('id', $batch->usuario_id)
                    ->orWhereHas('role', fn ($roleQuery) => $roleQuery->where('nombre', 'admin'));
            })
            ->pluck('email')
            ->unique()
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        $status = $batch->estado === 'completado' ? 'completado correctamente' : 'completado con errores';
        $templateName = e((string) ($batch->template?->nombre ?? 'Plantilla'));
        $sectionName = e(trim(($batch->section?->grado ?? '').' '.($batch->section?->nombre ?? '')));
        $body = '<h2>Envio masivo '.$status.'</h2>'
            .'<p><strong>Lote:</strong> '.e($batch->nombre).'</p>'
            .'<p><strong>Plantilla:</strong> '.$templateName.'<br><strong>Seccion:</strong> '.$sectionName.'</p>'
            .'<p><strong>Enviados:</strong> '.$batch->enviados.'<br><strong>Fallidos:</strong> '.$batch->fallidos.'<br><strong>Omitidos:</strong> '.$batch->omitidos.'</p>';

        Mail::html($body, function ($message) use ($recipients, $batch, $status): void {
            $message->to($recipients->all())
                ->subject('Envio masivo '.$status.': '.$batch->nombre);
        });
    }
}
