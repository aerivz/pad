<?php

use App\Jobs\GenerateSystemBackupJob;
use App\Models\SystemBackup;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Allows shared hosting to process queued school emails through one scheduler cron.
Schedule::command('queue:work database --queue=emails --stop-when-empty --tries=3 --timeout=180')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Requeues backups left pending by deployments where no backup worker existed.
Schedule::call(function (): void {
    SystemBackup::query()
        ->where('estado', 'procesando')
        ->where('started_at', '<=', now()->subMinutes(40))
        ->update([
            'estado' => 'pendiente',
            'error_message' => 'El proceso anterior se interrumpio y fue reprogramado automaticamente.',
            'started_at' => null,
        ]);

    SystemBackup::query()
        ->where('estado', 'pendiente')
        ->oldest('id')
        ->limit(10)
        ->pluck('id')
        ->each(fn (int $backupId) => GenerateSystemBackupJob::dispatch($backupId)
            ->onConnection('backups')
            ->onQueue('backups'));
})->name('backups:recover-pending')->everyMinute()->withoutOverlapping();

Schedule::command('queue:work backups --queue=backups --stop-when-empty --tries=1 --timeout=1800')
    ->everyMinute()
    ->withoutOverlapping(35)
    ->runInBackground();
