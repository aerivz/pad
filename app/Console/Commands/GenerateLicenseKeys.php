<?php

namespace App\Console\Commands;

use App\Support\LicenseKeyGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateLicenseKeys extends Command
{
    protected $signature = 'license:generate-keys
        {--private= : Ruta donde se guardara la llave privada}
        {--public= : Ruta donde se guardara la llave publica}';

    protected $description = 'Genera el par de llaves para firmar licencias';

    public function handle(): int
    {
        $privatePath = $this->absolutePath((string) ($this->option('private') ?: storage_path('app/private/license-signing-private.pem')));
        $publicPath = $this->absolutePath((string) ($this->option('public') ?: resource_path('keys/license-public.pem')));

        if (is_file($privatePath) || is_file($publicPath)) {
            $this->error('No se reemplazaron llaves existentes. Usa rutas nuevas o mueve primero los archivos actuales.');

            return self::FAILURE;
        }

        $keys = LicenseKeyGenerator::generate();

        File::ensureDirectoryExists(dirname($privatePath));
        File::ensureDirectoryExists(dirname($publicPath));
        File::put($privatePath, $keys['private'], true);
        @chmod($privatePath, 0600);
        File::put($publicPath, $keys['public'], true);

        $this->info('Llaves generadas correctamente.');
        $this->line('Privada: '.$privatePath);
        $this->line('Publica: '.$publicPath);
        $this->warn('Guarda la llave privada fuera del servidor del cliente y nunca la subas al repositorio.');

        return self::SUCCESS;
    }

    private function absolutePath(string $path): string
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1
            ? $path
            : base_path($path);
    }
}
