<?php

namespace App\Http\Controllers;

use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(AppUrl::route('dashboard'));
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nombre_usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['nombre_usuario' => $credentials['nombre_usuario'], 'password' => $credentials['password'], 'activo' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $intended = $request->session()->pull('url.intended');

            if (is_string($intended) && $intended !== '') {
                return redirect(AppUrl::menu($intended));
            }

            return redirect(AppUrl::route('dashboard'));
        }

        return back()
            ->withErrors(['nombre_usuario' => 'Las credenciales no son validas o el usuario esta inactivo.'])
            ->onlyInput('nombre_usuario');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(AppUrl::route('login'))->with('status', 'Sesion finalizada correctamente.');
    }
}
