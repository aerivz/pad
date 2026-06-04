<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class TelegramErrorNotifier
{
    public function send(Throwable $exception, ?Request $request = null): void
    {
        if (! $this->enabled() || ! $this->shouldNotify($exception)) {
            return;
        }

        $token = (string) config('services.telegram_errors.bot_token');
        $chatId = (string) config('services.telegram_errors.chat_id');

        if ($token === '' || $chatId === '') {
            return;
        }

        try {
            Http::asForm()
                ->timeout((int) config('services.telegram_errors.timeout', 5))
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $this->buildMessage($exception, $request),
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ])
                ->throw();
        } catch (Throwable $notificationException) {
            Log::warning('No se pudo enviar el error a Telegram.', [
                'message' => $notificationException->getMessage(),
            ]);
        }
    }

    private function enabled(): bool
    {
        return (bool) config('services.telegram_errors.enabled', false);
    }

    private function shouldNotify(Throwable $exception): bool
    {
        if ($exception instanceof HttpExceptionInterface) {
            return ! in_array($exception->getStatusCode(), [401, 403, 404, 419, 422], true);
        }

        return true;
    }

    private function buildMessage(Throwable $exception, ?Request $request = null): string
    {
        $environment = (string) config('app.env');
        $application = (string) config('app.name', 'Laravel');
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $requestLine = $request ? strtoupper($request->method()).' '.$request->fullUrl() : 'CLI / sin request';
        $userLine = $this->formatUser($request?->user());
        $location = $this->trimForTelegram($exception->getFile().':'.$exception->getLine(), 350);
        $message = $this->trimForTelegram($exception->getMessage() ?: 'Sin mensaje de error.', 900);
        $trace = $this->trimForTelegram($this->applicationTrace($exception), 1800);

        return implode("\n", array_filter([
            '<b>EduNotas - Error detectado</b>',
            '<b>App:</b> '.e($application),
            '<b>Entorno:</b> '.e($environment),
            '<b>HTTP:</b> '.e((string) $statusCode),
            '<b>Tipo:</b> '.e($exception::class),
            '<b>Ruta:</b> '.e($requestLine),
            '<b>Usuario:</b> '.e($userLine),
            '<b>Mensaje:</b> '.e($message),
            '<b>Archivo:</b> '.e($location),
            $trace !== '' ? '<b>Traza:</b>'."\n".e($trace) : null,
        ]));
    }

    private function applicationTrace(Throwable $exception): string
    {
        $frames = collect($exception->getTrace())
            ->map(function (array $frame): ?string {
                $file = $frame['file'] ?? null;

                if (! is_string($file) || ! Str::contains(str_replace('\\', '/', $file), '/app/')) {
                    return null;
                }

                $line = $frame['line'] ?? '?';
                $function = $frame['function'] ?? 'closure';

                return basename($file).':'.$line.' -> '.$function.'()';
            })
            ->filter()
            ->take(6)
            ->values();

        return $frames->implode("\n");
    }

    private function formatUser(?Authenticatable $user): string
    {
        if (! $user) {
            return 'Invitado';
        }

        $name = trim((string) ($user->nombres ?? '').' '.(string) ($user->apellidos ?? ''));
        $identifier = method_exists($user, 'getAuthIdentifier') ? $user->getAuthIdentifier() : null;

        return trim(($name !== '' ? $name : 'Usuario').' #'.$identifier);
    }

    private function trimForTelegram(string $value, int $limit): string
    {
        return Str::limit(trim($value), $limit, '...');
    }
}
