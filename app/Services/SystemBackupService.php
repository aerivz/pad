<?php

namespace App\Services;

use App\Models\SystemBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class SystemBackupService
{
    public function generate(SystemBackup $backup): void
    {
        $workingDirectory = storage_path('app/backups/tmp-'.$backup->id);
        $zipPath = storage_path('app/backups/backup-'.$backup->id.'-'.now()->format('Ymd-His').'.zip');

        File::ensureDirectoryExists(dirname($zipPath));
        File::ensureDirectoryExists($workingDirectory);

        $backup->forceFill([
            'estado' => 'procesando',
            'error_message' => null,
            'started_at' => now(),
            'finished_at' => null,
        ])->save();

        try {
            $databaseDumpPath = $workingDirectory.DIRECTORY_SEPARATOR.'database.sql';
            $manifestPath = $workingDirectory.DIRECTORY_SEPARATOR.'manifest.json';

            $this->dumpDatabase($databaseDumpPath);

            File::put($manifestPath, json_encode([
                'backup_id' => $backup->id,
                'nombre' => $backup->nombre,
                'generado_en' => now()->toDateTimeString(),
                'entorno' => config('app.env'),
                'base_datos' => config('database.connections.'.config('database.default').'.database'),
                'incluye' => [
                    'database/database.sql',
                    'public/uploads',
                    'public/images',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $totalFiles = $this->createZip($zipPath, [
                ['source' => $databaseDumpPath, 'target' => 'database/database.sql', 'type' => 'file'],
                ['source' => $manifestPath, 'target' => 'manifest.json', 'type' => 'file'],
                ['source' => public_path('uploads'), 'target' => 'public/uploads', 'type' => 'directory'],
                ['source' => public_path('images'), 'target' => 'public/images', 'type' => 'directory'],
            ]);

            $backup->forceFill([
                'archivo_zip' => $zipPath,
                'estado' => 'completado',
                'tamano_bytes' => File::size($zipPath),
                'total_archivos' => $totalFiles,
                'metadatos' => [
                    'incluye_uploads' => is_dir(public_path('uploads')),
                    'incluye_imagenes' => is_dir(public_path('images')),
                ],
                'finished_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $backup->forceFill([
                'estado' => 'fallido',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ])->save();
        } finally {
            File::deleteDirectory($workingDirectory);
        }
    }

    private function createZip(string $zipPath, array $entries): int
    {
        $zip = new ZipArchive();
        $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            throw new \RuntimeException('No fue posible crear el archivo ZIP del respaldo.');
        }

        $count = 0;

        foreach ($entries as $entry) {
            if (($entry['type'] ?? 'file') === 'directory') {
                $count += $this->addDirectoryToZip($zip, $entry['source'], $entry['target']);
                continue;
            }

            if (is_file($entry['source'])) {
                $zip->addFile($entry['source'], $entry['target']);
                $count++;
            }
        }

        $zip->close();

        return $count;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $source, string $target): int
    {
        if (! is_dir($source)) {
            return 0;
        }

        $count = 0;
        $files = File::allFiles($source);

        foreach ($files as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($file->getRealPath(), trim($target, '/').'/'.$relativePath);
            $count++;
        }

        return $count;
    }

    private function dumpDatabase(string $destination): void
    {
        if ($this->tryMysqlDump($destination)) {
            return;
        }

        $this->dumpDatabaseWithPhp($destination);
    }

    private function tryMysqlDump(string $destination): bool
    {
        $connection = config('database.connections.'.config('database.default'));
        $database = $connection['database'] ?? null;
        $host = $connection['host'] ?? '127.0.0.1';
        $port = (string) ($connection['port'] ?? '3306');
        $username = $connection['username'] ?? null;
        $password = (string) ($connection['password'] ?? '');

        if (! $database || ! $username) {
            return false;
        }

        foreach ($this->mysqlDumpCandidates() as $binary) {
            if ($binary !== 'mysqldump' && ! is_file($binary)) {
                continue;
            }

            $command = [
                $binary,
                '--host='.$host,
                '--port='.$port,
                '--user='.$username,
                '--password='.$password,
                '--skip-comments',
                '--single-transaction',
                '--routines',
                '--triggers',
                $database,
            ];

            $process = new Process($command, base_path(), null, null, 300);
            $process->run();

            if (! $process->isSuccessful()) {
                continue;
            }

            File::put($destination, $process->getOutput());

            return is_file($destination) && File::size($destination) > 0;
        }

        return false;
    }

    private function mysqlDumpCandidates(): array
    {
        $candidates = array_filter([
            env('MYSQLDUMP_BINARY'),
            dirname(dirname(base_path())).DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysqldump.exe',
            'mysqldump',
        ]);

        return array_values(array_unique($candidates));
    }

    private function dumpDatabaseWithPhp(string $destination): void
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        $pdo = $connection->getPdo();
        $tableKey = 'Tables_in_'.$database;
        $tables = collect(DB::select('SHOW TABLES'))->pluck($tableKey)->filter()->values();

        $sql = [];
        $sql[] = '-- Respaldo generado por EduNotas';
        $sql[] = '-- Fecha: '.now()->toDateTimeString();
        $sql[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $sql[] = '';

        foreach ($tables as $table) {
            $createRow = DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
            $createKey = 'Create Table';

            $sql[] = '-- Tabla: '.$table;
            $sql[] = 'DROP TABLE IF EXISTS `'.$table.'`;';
            $sql[] = $createRow->$createKey.';';
            $sql[] = '';

            foreach (DB::table($table)->get() as $row) {
                $data = (array) $row;
                $columns = array_map(fn ($column) => '`'.$column.'`', array_keys($data));
                $values = array_map(function ($value) use ($pdo) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    if (is_bool($value)) {
                        return $value ? '1' : '0';
                    }

                    if (is_int($value) || is_float($value)) {
                        return (string) $value;
                    }

                    return $pdo->quote((string) $value);
                }, array_values($data));

                $sql[] = 'INSERT INTO `'.$table.'` ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).');';
            }

            $sql[] = '';
        }

        $sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';

        File::put($destination, implode(PHP_EOL, $sql));
    }
}
