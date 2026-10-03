<?php

namespace Tests\Unit;

use App\Http\Controllers\SystemConfigurationController;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class SystemConfigurationControllerTest extends TestCase
{
    public function test_smtp_error_includes_connection_details_and_hides_password(): void
    {
        config([
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.scheme' => 'smtp',
            'mail.mailers.smtp.password' => 'secret-password',
        ]);

        $exception = new RuntimeException(
            'No se pudo autenticar.',
            0,
            new RuntimeException('Credencial rechazada: secret-password')
        );
        $method = new ReflectionMethod(SystemConfigurationController::class, 'smtpFailureMessage');

        $message = $method->invoke(new SystemConfigurationController, $exception);

        $this->assertStringContainsString('smtp.example.test:587 (smtp)', $message);
        $this->assertStringContainsString('No se pudo autenticar.', $message);
        $this->assertStringContainsString('[contrasena oculta]', $message);
        $this->assertStringNotContainsString('secret-password', $message);
    }
}
