<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LicenseService;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class LicenseController extends Controller
{
    public function update(Request $request, LicenseService $licenses): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->role()->value('nombre') === 'admin', 403);

        $data = $request->validate([
            'license_token' => ['required', 'string', 'max:12000'],
        ]);

        try {
            $status = $licenses->install($data['license_token']);
        } catch (RuntimeException $exception) {
            return redirect(AppUrl::route('config.index').'#license')
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('No se pudo instalar la licencia de la aplicacion.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return redirect(AppUrl::route('config.index').'#license')
                ->withInput()
                ->with('error', 'La licencia es valida, pero no pudo guardarse en el servidor. Revisa los permisos de almacenamiento.');
        }

        Log::notice('Licencia de la aplicacion instalada.', [
            'user_id' => $user->id,
            'license_id' => $status['payload']['license_id'] ?? null,
            'customer' => $status['payload']['customer'] ?? null,
            'expires_at' => $status['payload']['expires_at'] ?? null,
        ]);

        return redirect(AppUrl::route('config.index').'#license')
            ->with('status', $status['message']);
    }
}
