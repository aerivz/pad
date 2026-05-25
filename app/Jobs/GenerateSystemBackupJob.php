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
        $backup = SystemBackup::query()->find($this->backupId);

        if (! $backup) {
            return;
        }

        $service->generate($backup);
    }
}
