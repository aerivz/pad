<?php

namespace App\Jobs;

use App\Models\EmailBatch;
use App\Models\EmailDispatch;
use App\Models\User;
use App\Services\EmailDeliveryService;
use App\Services\SystemSettingsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class SendEmailDispatchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 180;
    public array $backoff = [60, 300];
    public bool $afterCommit = true;

    public function __construct(public int $dispatchId)
    {
        $this->onQueue('emails');
    }

    public function handle(EmailDeliveryService $deliveryService, SystemSettingsService $settings): void
    {
        $limit = max(1, (int) $settings->value('mail.rate_limit_per_minute', 30));

        if (! RateLimiter::attempt('emails:global-rate', $limit, static fn () => true, 60)) {
            $this->release(10);

            return;
        }

        $dispatch = EmailDispatch::query()->find($this->dispatchId);

        if (! $dispatch || ! $dispatch->activo || $dispatch->estado === 'enviado') {
            return;
        }

        try {
            $result = $deliveryService->sendDispatch($dispatch, User::query()->find($dispatch->usuario_id));

            $dispatch->update([
                'estado' => 'enviado',
                'en_cola' => false,
                'destinatario_email' => $result['recipient'],
                'adjuntos_generados' => $result['attachments'],
                'error_mensaje' => null,
                'enviado_en' => now(),
            ]);

            $this->completeBatch($dispatch, true);
        } catch (Throwable $exception) {
            $dispatch->update(['error_mensaje' => Str::limit($exception->getMessage(), 2000)]);
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $dispatch = EmailDispatch::query()->find($this->dispatchId);

        if (! $dispatch || $dispatch->estado === 'enviado') {
            return;
        }

        $dispatch->update([
            'estado' => 'fallido',
            'en_cola' => false,
            'error_mensaje' => Str::limit($exception?->getMessage() ?? 'El envio no pudo completarse.', 2000),
        ]);

        $this->completeBatch($dispatch, false);
    }

    private function completeBatch(EmailDispatch $dispatch, bool $sent): void
    {
        if (! $dispatch->lote_id) {
            return;
        }

        $notifyBatchId = DB::transaction(function () use ($dispatch, $sent): ?int {
            $batch = EmailBatch::query()->lockForUpdate()->find($dispatch->lote_id);

            if (! $batch) {
                return null;
            }

            $batch->procesados++;
            $sent ? $batch->enviados++ : $batch->fallidos++;

            if ($batch->procesados >= $batch->total) {
                $batch->estado = $batch->fallidos > 0 ? 'completado_con_errores' : 'completado';
                $batch->finalizado_en = now();

                if ($batch->notificado_en === null) {
                    $batch->notificado_en = now();
                    $batch->save();

                    return $batch->id;
                }
            }

            $batch->save();

            return null;
        });

        if ($notifyBatchId !== null) {
            NotifyEmailBatchCompletedJob::dispatch($notifyBatchId)
                ->onConnection('database')
                ->onQueue('emails');
        }
    }
}
