<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemSettingsService
{
    private ?array $settings = null;

    public function apply(): void
    {
        $settings = $this->all();
        if ($settings === []) return;

        config([
            'mail.default' => $settings['mail.mailer'] ?? config('mail.default'),
            'mail.mailers.smtp.host' => $settings['mail.host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => isset($settings['mail.port']) ? (int) $settings['mail.port'] : config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => $settings['mail.username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $settings['mail.password'] ?? config('mail.mailers.smtp.password'),
            'mail.mailers.smtp.scheme' => ($settings['mail.scheme'] ?? null) ?: config('mail.mailers.smtp.scheme'),
            'mail.from.address' => $settings['mail.from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $settings['mail.from_name'] ?? config('mail.from.name'),
            'services.telegram_errors.enabled' => isset($settings['telegram.enabled']) ? filter_var($settings['telegram.enabled'], FILTER_VALIDATE_BOOL) : config('services.telegram_errors.enabled'),
            'services.telegram_errors.bot_token' => $settings['telegram.bot_token'] ?? config('services.telegram_errors.bot_token'),
            'services.telegram_errors.chat_id' => $settings['telegram.chat_id'] ?? config('services.telegram_errors.chat_id'),
            'services.telegram_errors.timeout' => isset($settings['telegram.timeout']) ? (int) $settings['telegram.timeout'] : config('services.telegram_errors.timeout'),
        ]);
    }

    public function value(string $key, mixed $default = null): mixed { return $this->all()[$key] ?? $default; }
    public function has(string $key): bool { return array_key_exists($key, $this->all()); }

    public function put(string $key, mixed $value, bool $encrypted = false): void
    {
        $storedValue = (string) $value;
        if ($encrypted && $storedValue !== '') $storedValue = Crypt::encryptString($storedValue);
        SystemSetting::query()->updateOrCreate(['clave' => $key], ['valor' => $storedValue, 'cifrado' => $encrypted]);
        $this->settings = null;
    }

    public function publicValues(): array
    {
        return [
            'mail_mailer' => $this->value('mail.mailer', config('mail.default')), 'mail_host' => $this->value('mail.host', config('mail.mailers.smtp.host')),
            'mail_port' => $this->value('mail.port', config('mail.mailers.smtp.port')), 'mail_username' => $this->value('mail.username', config('mail.mailers.smtp.username')),
            'mail_scheme' => $this->value('mail.scheme', config('mail.mailers.smtp.scheme')), 'mail_from_address' => $this->value('mail.from_address', config('mail.from.address')),
            'mail_from_name' => $this->value('mail.from_name', config('mail.from.name')), 'telegram_enabled' => filter_var($this->value('telegram.enabled', config('services.telegram_errors.enabled')), FILTER_VALIDATE_BOOL),
            'telegram_chat_id' => $this->value('telegram.chat_id', config('services.telegram_errors.chat_id')), 'telegram_timeout' => $this->value('telegram.timeout', config('services.telegram_errors.timeout', 5)),
            'mail_password_configured' => $this->has('mail.password') || filled(config('mail.mailers.smtp.password')), 'telegram_token_configured' => $this->has('telegram.bot_token') || filled(config('services.telegram_errors.bot_token')),
        ];
    }

    private function all(): array
    {
        if ($this->settings !== null) return $this->settings;
        try {
            if (!Schema::hasTable('configuraciones_sistema')) return $this->settings = [];
            return $this->settings = SystemSetting::query()->get(['clave', 'valor', 'cifrado'])->mapWithKeys(function (SystemSetting $setting): array {
                $value = $setting->valor;
                if ($setting->cifrado && $value !== null && $value !== '') {
                    try { $value = Crypt::decryptString($value); } catch (Throwable) { $value = null; }
                }
                return [$setting->clave => $value];
            })->all();
        } catch (Throwable) { return $this->settings = []; }
    }
}
