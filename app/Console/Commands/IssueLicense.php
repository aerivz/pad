<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class IssueLicense extends Command
{
    protected $signature = 'license:issue
        {customer : Nombre del cliente}
        {expires : Fecha en que iniciara el modo solo lectura YYYY-MM-DD}
        {--installation= : Identificador o dominio autorizado}
        {--timezone= : Zona horaria aplicable a la fecha de vencimiento}
        {--private= : Ruta absoluta de la llave privada}
        {--output= : Archivo opcional para guardar la licencia}';

    protected $description = 'Genera una licencia firmada para una instalacion';

    public function handle(): int
    {
        $privatePath = trim((string) $this->option('private'));
        $installation = trim((string) ($this->option('installation') ?: config('license.installation')));
        $timezone = trim((string) ($this->option('timezone') ?: config('app.timezone')));

        if ($privatePath === '' || ! is_file($privatePath)) {
            $this->error('Indica una llave privada existente con --private.');

            return self::FAILURE;
        }

        if ($installation === '') {
            $this->error('Indica la instalacion autorizada con --installation.');

            return self::FAILURE;
        }

        try {
            $expiresAt = CarbonImmutable::createFromFormat('!Y-m-d', (string) $this->argument('expires'), $timezone);

            if ($expiresAt === false || $expiresAt->format('Y-m-d') !== $this->argument('expires')) {
                throw new \InvalidArgumentException;
            }

            $token = LicenseService::issue([
                'version' => 1,
                'license_id' => (string) Str::uuid(),
                'customer' => trim((string) $this->argument('customer')),
                'installation' => $installation,
                'issued_at' => CarbonImmutable::now()->utc()->toIso8601String(),
                'expires_at' => $expiresAt->startOfDay()->utc()->toIso8601String(),
            ], (string) file_get_contents($privatePath));
        } catch (Throwable $exception) {
            $this->error('No se pudo generar la licencia: '.$exception->getMessage());

            return self::FAILURE;
        }

        $outputPath = trim((string) $this->option('output'));

        if ($outputPath !== '') {
            File::ensureDirectoryExists(dirname($outputPath));
            File::put($outputPath, $token.PHP_EOL, true);
            $this->info('Licencia guardada en '.$outputPath);
        } else {
            $this->line($token);
        }

        return self::SUCCESS;
    }
}
