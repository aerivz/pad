<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureLicenseAllowsChanges;
use App\Services\LicenseService;
use App\Support\LicenseKeyGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class LicenseServiceTest extends TestCase
{
    private string $temporaryDirectory;
    private string $privateKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = storage_path('framework/testing/license-'.bin2hex(random_bytes(6)));
        File::ensureDirectoryExists($this->temporaryDirectory);

        $keys = LicenseKeyGenerator::generate(2048);
        $this->privateKey = $keys['private'];
        file_put_contents($this->temporaryDirectory.'/public.pem', $keys['public']);

        config([
            'license.enforced' => true,
            'license.installation' => 'school.test',
            'license.public_key_path' => $this->temporaryDirectory.'/public.pem',
            'license.license_path' => $this->temporaryDirectory.'/application.license',
            'license.warning_days' => 15,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->temporaryDirectory);
        parent::tearDown();
    }

    public function test_valid_signed_license_allows_changes(): void
    {
        $now = CarbonImmutable::parse('2026-10-26 12:00:00 UTC');
        file_put_contents(config('license.license_path'), $this->token('2026-12-31T23:59:59Z'));

        $status = app(LicenseService::class)->status($now);

        $this->assertSame('active', $status['state']);
        $this->assertTrue($status['allows_changes']);
        $this->assertSame('Colegio de prueba', $status['payload']['customer']);
    }

    public function test_expired_license_enables_read_only_mode(): void
    {
        file_put_contents(config('license.license_path'), $this->token('2026-10-25T23:59:59Z'));
        $service = app(LicenseService::class);
        $now = CarbonImmutable::parse('2026-10-26 12:00:00 UTC');

        $status = $service->status($now);

        $this->assertSame('expired', $status['state']);
        $this->assertFalse($status['allows_changes']);
    }

    public function test_tampered_license_is_rejected(): void
    {
        $token = $this->token('2026-12-31T23:59:59Z');
        [$payload, $signature] = explode('.', $token);
        $payload[0] = $payload[0] === 'A' ? 'B' : 'A';
        file_put_contents(config('license.license_path'), $payload.'.'.$signature);

        $status = app(LicenseService::class)->status();

        $this->assertSame('invalid', $status['state']);
        $this->assertFalse($status['allows_changes']);
    }

    public function test_read_only_middleware_allows_get_and_rejects_post(): void
    {
        file_put_contents(config('license.license_path'), $this->token('2020-01-01T23:59:59Z'));
        $middleware = new EnsureLicenseAllowsChanges(app(LicenseService::class));

        $getResponse = $middleware->handle(Request::create('/alumnos', 'GET'), fn () => response('ok'));
        $postRequest = Request::create('/alumnos', 'POST', server: ['HTTP_ACCEPT' => 'application/json']);
        $postResponse = $middleware->handle($postRequest, fn () => response('changed'));

        $this->assertSame(200, $getResponse->getStatusCode());
        $this->assertSame(423, $postResponse->getStatusCode());
        $this->assertSame('expired', $postResponse->getData(true)['license_state']);
    }

    public function test_invalid_license_cannot_be_installed(): void
    {
        $this->expectException(RuntimeException::class);

        app(LicenseService::class)->install('licencia.alterada');
    }

    public function test_valid_license_can_be_renewed(): void
    {
        $service = app(LicenseService::class);
        $service->install($this->token('2026-12-31T23:59:59Z'));
        $service->install($this->token('2027-12-31T23:59:59Z'));

        $this->assertStringContainsString('2027-12-31', $service->status()['payload']['expires_at']);
    }

    private function token(string $expiresAt): string
    {
        return LicenseService::issue([
            'version' => 1,
            'license_id' => 'license-test',
            'customer' => 'Colegio de prueba',
            'installation' => 'school.test',
            'issued_at' => '2026-01-01T00:00:00Z',
            'expires_at' => $expiresAt,
        ], $this->privateKey);
    }
}
