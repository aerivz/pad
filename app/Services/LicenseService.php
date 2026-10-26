<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class LicenseService
{
    public function status(?CarbonImmutable $now = null): array
    {
        if (! config('license.enforced')) {
            return $this->result('disabled', true, 'El control de licencia no esta habilitado.');
        }

        $path = (string) config('license.license_path');

        if (! is_file($path)) {
            return $this->result('missing', false, 'No hay una licencia instalada.');
        }

        return $this->validateToken(trim((string) file_get_contents($path)), $now);
    }

    public function validateToken(string $token, ?CarbonImmutable $now = null): array
    {
        $parts = explode('.', trim($token));

        if (count($parts) !== 2) {
            return $this->result('invalid', false, 'El formato de la licencia no es valido.');
        }

        [$encodedPayload, $encodedSignature] = $parts;
        $payloadJson = $this->base64UrlDecode($encodedPayload);
        $signature = $this->base64UrlDecode($encodedSignature);

        if ($payloadJson === null || $signature === null) {
            return $this->result('invalid', false, 'La licencia contiene datos invalidos.');
        }

        $publicKeyPath = (string) config('license.public_key_path');

        if (! is_file($publicKeyPath)) {
            return $this->result('invalid', false, 'La llave publica de licencias no esta configurada.');
        }

        $publicKey = openssl_pkey_get_public((string) file_get_contents($publicKeyPath));

        if ($publicKey === false || openssl_verify($payloadJson, $signature, $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
            return $this->result('invalid', false, 'La firma de la licencia no es valida.');
        }

        try {
            $payload = json_decode($payloadJson, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return $this->result('invalid', false, 'No se pudo leer la licencia.');
        }

        if (! is_array($payload)
            || ($payload['version'] ?? null) !== 1
            || ! filled($payload['license_id'] ?? null)
            || ! filled($payload['customer'] ?? null)
            || ! filled($payload['installation'] ?? null)
            || ! filled($payload['expires_at'] ?? null)) {
            return $this->result('invalid', false, 'La licencia no contiene todos los datos requeridos.');
        }

        $installation = trim((string) config('license.installation'));

        if ($installation === '' || ! hash_equals($installation, (string) $payload['installation'])) {
            return $this->result('invalid', false, 'La licencia pertenece a otra instalacion.', $payload);
        }

        try {
            $expiresAt = CarbonImmutable::parse((string) $payload['expires_at'])->utc();
        } catch (Throwable) {
            return $this->result('invalid', false, 'La fecha de vencimiento no es valida.', $payload);
        }

        $now = ($now ?? CarbonImmutable::now())->utc();
        $payload['expires_at'] = $expiresAt->toIso8601String();

        if ($now->greaterThan($expiresAt)) {
            return $this->result('expired', false, 'La licencia vencio el '.$expiresAt->format('d/m/Y').'.', $payload, $expiresAt);
        }

        $daysRemaining = (int) max(0, $now->startOfDay()->diffInDays($expiresAt->startOfDay(), false));
        $state = $daysRemaining <= max(0, (int) config('license.warning_days')) ? 'expiring' : 'active';
        $message = $state === 'expiring'
            ? 'La licencia vence el '.$expiresAt->format('d/m/Y').' ('.$daysRemaining.' dias restantes).'
            : 'Licencia vigente hasta el '.$expiresAt->format('d/m/Y').'.';

        return $this->result($state, true, $message, $payload, $expiresAt, $daysRemaining);
    }

    public function install(string $token): array
    {
        $status = $this->validateToken($token);

        if (! $status['allows_changes']) {
            throw new RuntimeException($status['message']);
        }

        $path = (string) config('license.license_path');
        File::ensureDirectoryExists(dirname($path));
        $temporaryPath = $path.'.'.Str::random(12).'.tmp';

        if (file_put_contents($temporaryPath, trim($token).PHP_EOL, LOCK_EX) === false || ! rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('No se pudo guardar la licencia en el servidor.');
        }

        return $status;
    }

    public static function issue(array $payload, string $privateKeyPem): string
    {
        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $privateKey = openssl_pkey_get_private($privateKeyPem);

        if ($privateKey === false || ! openssl_sign($payloadJson, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('No se pudo firmar la licencia con la llave privada.');
        }

        return self::base64UrlEncode($payloadJson).'.'.self::base64UrlEncode($signature);
    }

    private function result(
        string $state,
        bool $allowsChanges,
        string $message,
        array $payload = [],
        ?CarbonImmutable $expiresAt = null,
        ?int $daysRemaining = null
    ): array {
        return [
            'state' => $state,
            'allows_changes' => $allowsChanges,
            'message' => $message,
            'payload' => $payload,
            'expires_at' => $expiresAt,
            'days_remaining' => $daysRemaining,
        ];
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
            return null;
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
