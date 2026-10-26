<?php

namespace App\Services;

use Illuminate\Support\Str;
use SplFileObject;

class ApplicationLogReader
{
    private const ERROR_LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __construct(private ?string $directory = null)
    {
        $this->directory ??= storage_path('logs');
    }

    public function errorsBetween(string $from, string $to, int $limit = 250): array
    {
        $entries = [];
        $matched = 0;

        foreach ($this->logFiles() as $path) {
            $current = null;
            $file = new SplFileObject($path, 'r');

            while (! $file->eof()) {
                $line = $file->fgets();

                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([^.]+)\.([A-Z]+):\s?(.*)$/', rtrim($line, "\r\n"), $matches)) {
                    $this->appendEntry($entries, $matched, $current, $from, $to, basename($path));
                    $current = [
                        'timestamp' => $matches[1],
                        'environment' => $matches[2],
                        'level' => $matches[3],
                        'content' => $matches[4],
                    ];

                    continue;
                }

                if ($current !== null && $line !== '') {
                    $current['content'] .= "\n".$line;
                }
            }

            $this->appendEntry($entries, $matched, $current, $from, $to, basename($path));
        }

        usort($entries, fn (array $left, array $right): int => strcmp($right['timestamp'], $left['timestamp']));

        return [
            'entries' => array_slice($entries, 0, $limit),
            'total' => $matched,
            'truncated' => $matched > $limit,
        ];
    }

    private function appendEntry(array &$entries, int &$matched, ?array $entry, string $from, string $to, string $file): void
    {
        if ($entry === null || ! in_array($entry['level'], self::ERROR_LEVELS, true)) {
            return;
        }

        $date = substr($entry['timestamp'], 0, 10);

        if ($date < $from || $date > $to) {
            return;
        }

        $matched++;
        $details = $this->redact(Str::limit(trim($entry['content']), 20000, "\n[detalle recortado]"));
        $entries[] = [
            'timestamp' => $entry['timestamp'],
            'environment' => $entry['environment'],
            'level' => $entry['level'],
            'message' => Str::limit((string) strtok($details, "\r\n"), 300),
            'details' => $details,
            'file' => $file,
        ];
    }

    private function logFiles(): array
    {
        if (! is_dir($this->directory)) {
            return [];
        }

        $files = glob($this->directory.DIRECTORY_SEPARATOR.'laravel*.log') ?: [];
        sort($files);

        return array_values(array_filter($files, 'is_readable'));
    }

    private function redact(string $content): string
    {
        $secrets = array_filter([
            config('app.key'),
            config('mail.mailers.smtp.password'),
            config('services.telegram_errors.bot_token'),
        ], fn ($value): bool => is_string($value) && $value !== '');

        if ($secrets !== []) {
            $content = str_replace($secrets, '[valor oculto]', $content);
        }

        return (string) preg_replace(
            '/(password|contrase(?:n|ñ)a|secret|token|authorization)(["\']?\s*[:=]\s*["\']?)[^"\'\s,}\]]+/iu',
            '$1$2[valor oculto]',
            $content
        );
    }
}
