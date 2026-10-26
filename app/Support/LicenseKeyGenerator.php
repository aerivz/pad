<?php

namespace App\Support;

use RuntimeException;

class LicenseKeyGenerator
{
    public static function generate(int $bits = 3072): array
    {
        $options = [
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $configPath = self::configurationPath();

        if ($configPath !== null) {
            $options['config'] = $configPath;
        }

        $key = openssl_pkey_new($options);

        if ($key === false || ! openssl_pkey_export($key, $privateKey, null, $options)) {
            throw new RuntimeException('OpenSSL no pudo generar la llave privada. '.self::lastError());
        }

        $details = openssl_pkey_get_details($key);

        if (! is_array($details) || empty($details['key'])) {
            throw new RuntimeException('OpenSSL no pudo obtener la llave publica. '.self::lastError());
        }

        return [
            'private' => $privateKey,
            'public' => $details['key'],
        ];
    }

    private static function configurationPath(): ?string
    {
        $phpDirectory = dirname(PHP_BINARY);
        $configured = getenv('OPENSSL_CONF');
        $candidates = array_filter([
            is_string($configured) ? $configured : null,
            $phpDirectory.DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf',
            dirname($phpDirectory).DIRECTORY_SEPARATOR.'apache'.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'openssl.cnf',
            '/etc/ssl/openssl.cnf',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function lastError(): string
    {
        $errors = [];

        while (($error = openssl_error_string()) !== false) {
            $errors[] = $error;
        }

        return implode(' | ', array_unique($errors));
    }
}
