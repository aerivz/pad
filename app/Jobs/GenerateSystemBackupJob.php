<?php

namespace App\Jobs;

use App\Models\SystemBackup;
use App\Services\SystemBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateSystemBackupJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(public int $backupId)
    {
        $this->onQueue('backups');
    }

    public function handle(SystemBackupService $service): void
    {
        $claimed = SystemBackup::query()
            ->whereKey($this->backupId)
            ->where('estado', 'pendiente')
            ->update([
                'estado' => 'procesando',
                'error_message' => null,
                'started_at' => now(),
                'finished_at' => null,
            ]);

        if ($claimed === 0) {
            return;
        }

        $service->generate(SystemBackup::query()->findOrFail($this->backupId));
    }
}
