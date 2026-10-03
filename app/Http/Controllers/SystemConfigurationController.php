<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SystemSettingsService;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class SystemConfigurationController extends Controller
{
    public function update(Request $request, SystemSettingsService $settings): RedirectResponse
    {
        $this->authorizeAdministrator();
        $data = $this->validateSettings($request);
        foreach (['mail_mailer' => 'mail.mailer', 'mail_host' => 'mail.host', 'mail_port' => 'mail.port', 'mail_username' => 'mail.username', 'mail_scheme' => 'mail.scheme', 'mail_from_address' => 'mail.from.address', 'mail_from_name' => 'mail.from.name', 'mail_rate_limit_per_minute' => 'mail.rate_limit_per_minute', 'telegram_chat_id' => 'telegram.chat_id', 'telegram_timeout' => 'telegram.timeout'] as $input => $key) {
            $settings->put($key, $data[$input] ?? '');
        }
        $settings->put('telegram.enabled', $request->boolean('telegram_enabled') ? '1' : '0');
        if ($request->filled('mail_password')) {
            $settings->put('mail.password', $data['mail_password'], true);
        }
        if ($request->filled('telegram_bot_token')) {
            $settings->put('telegram.bot_token', $data['telegram_bot_token'], true);
        }
        $settings->apply();
        Mail::purge('smtp');

        return redirect(AppUrl::route('config.index'))->with('status', 'Configuracion guardada. Las credenciales sensibles se almacenaron cifradas.');
    }

    public function sendTest(Request $request, SystemSettingsService $settings): RedirectResponse
    {
        $this->authorizeAdministrator();
        $data = $request->validate(['destinatario' => ['required', 'email', 'max:150']]);
        $settings->apply();
        Mail::purge('smtp');

        if (config('mail.default') !== 'smtp') {
            return redirect(AppUrl::route('config.index'))
                ->withInput()
                ->with('error', 'Selecciona SMTP y guarda la configuracion antes de enviar una prueba real.');
        }

        try {
            Mail::raw('Este es un correo de prueba enviado desde ACI Notas. La configuracion SMTP funciona correctamente.', function ($message) use ($data): void {
                $message->to($data['destinatario'])->subject('Prueba de correo - ACI Notas');
            });
        } catch (Throwable $exception) {
            Log::error('Fallo la prueba de correo SMTP.', [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'scheme' => config('mail.mailers.smtp.scheme'),
                'recipient' => $data['destinatario'],
                'exception' => $exception,
            ]);

            return redirect(AppUrl::route('config.index'))
                ->withInput()
                ->with('error', $this->smtpFailureMessage($exception));
        }

        return redirect(AppUrl::route('config.index'))->with('status', 'Correo de prueba enviado a '.$data['destinatario'].'.');
    }

    private function smtpFailureMessage(Throwable $exception): string
    {
        $messages = [];
        $current = $exception;

        do {
            if ($current->getMessage() !== '') {
                $messages[] = $current->getMessage();
            }

            $current = $current->getPrevious();
        } while ($current instanceof Throwable);

        $detail = implode(' | ', array_values(array_unique($messages)));
        $password = (string) config('mail.mailers.smtp.password');

        if ($password !== '') {
            $detail = str_replace($password, '[contrasena oculta]', $detail);
        }

        $host = (string) config('mail.mailers.smtp.host');
        $port = (string) config('mail.mailers.smtp.port');
        $scheme = (string) (config('mail.mailers.smtp.scheme') ?: 'sin seguridad');

        return 'No se pudo enviar el correo de prueba mediante '.$host.':'.$port.' ('.$scheme.'). Detalle: '.Str::limit($detail, 900);
    }

    private function authorizeAdministrator(): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->role()->value('nombre') === 'admin', 403);
    }

    private function validateSettings(Request $request): array
    {
        return $request->validate([
            'mail_mailer' => ['required', Rule::in(['smtp', 'log'])], 'mail_host' => ['nullable', 'string', 'max:150'], 'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:150'], 'mail_password' => ['nullable', 'string', 'max:255'], 'mail_scheme' => ['nullable', Rule::in(['tls', 'ssl'])],
            'mail_from_address' => ['nullable', 'email', 'max:150'], 'mail_from_name' => ['nullable', 'string', 'max:150'], 'mail_rate_limit_per_minute' => ['required', 'integer', 'min:1', 'max:300'], 'telegram_bot_token' => ['nullable', 'string', 'max:255'],
            'telegram_chat_id' => ['nullable', 'string', 'max:100'], 'telegram_timeout' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);
    }
}
