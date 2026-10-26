<?php

return [
    'enforced' => (bool) env('APP_LICENSE_ENFORCED', false),
    'installation' => env('APP_LICENSE_INSTALLATION', parse_url((string) env('APP_URL', ''), PHP_URL_HOST)),
    'public_key_path' => env('APP_LICENSE_PUBLIC_KEY_PATH') ?: resource_path('keys/license-public.pem'),
    'license_path' => env('APP_LICENSE_PATH') ?: storage_path('app/private/application.license'),
    'warning_days' => (int) env('APP_LICENSE_WARNING_DAYS', 15),
];
