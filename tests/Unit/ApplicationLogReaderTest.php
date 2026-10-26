<?php

namespace Tests\Unit;

use App\Services\ApplicationLogReader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApplicationLogReaderTest extends TestCase
{
    public function test_it_reads_multiline_errors_in_range_and_redacts_secrets(): void
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pad-log-reader-'.Str::uuid();
        File::ensureDirectoryExists($directory);
        File::put($directory.DIRECTORY_SEPARATOR.'laravel.log', implode("\n", [
            '[2026-10-23 08:15:00] local.ERROR: Fallo SMTP password=super-secret {"token":"abc123"}',
            '#0 C:\\app\\Service.php(10): enviar()',
            '[2026-10-23 09:00:00] local.WARNING: Advertencia que no debe aparecer',
            '[2026-10-24 10:00:00] local.ERROR: Error fuera del rango',
        ]));

        try {
            $result = (new ApplicationLogReader($directory))->errorsBetween('2026-10-23', '2026-10-23');
        } finally {
            File::deleteDirectory($directory);
        }

        $this->assertSame(1, $result['total']);
        $this->assertFalse($result['truncated']);
        $this->assertStringContainsString('Fallo SMTP', $result['entries'][0]['message']);
        $this->assertStringContainsString('#0 C:\\app\\Service.php', $result['entries'][0]['details']);
        $this->assertStringNotContainsString('super-secret', $result['entries'][0]['details']);
        $this->assertStringNotContainsString('abc123', $result['entries'][0]['details']);
    }
}
