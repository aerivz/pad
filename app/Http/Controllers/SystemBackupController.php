<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateSystemBackupJob;
use App\Models\SystemBackup;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemBackupController extends Controller
{
    public function store(): RedirectResponse
    {
        $backup = SystemBackup::query()->create([
            'usuario_id' => auth()->id(),
            'nombre' => 'Respaldo '.now()->format('d-m-Y H:i:s'),
            'estado' => 'pendiente',
        ]);

        GenerateSystemBackupJob::dispatch($backup->id)->onConnection('database')->onQueue('backups');

        return redirect()
            ->route('backups.index')
            ->with('status', 'El respaldo fue enviado a segundo plano. En unos momentos podras descargar el ZIP.');
    }

    public function download(SystemBackup $backup): BinaryFileResponse|RedirectResponse
    {
        if (! $backup->isReady() || ! is_file($backup->archivo_zip)) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'El respaldo todavia no esta disponible para descarga.');
        }

        return response()->download($backup->archivo_zip, basename($backup->archivo_zip));
    }
}
