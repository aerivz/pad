<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rol_id' => ['required', 'exists:roles,id'],
            'nombre_usuario' => ['required', 'string', 'max:60', 'unique:usuarios,nombre_usuario'],
            'email' => ['required', 'email', 'max:150', 'unique:usuarios,email'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'rol_id' => $data['rol_id'],
            'nombre_usuario' => $data['nombre_usuario'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'activo' => true,
        ]);

        return redirect('/pad/usuarios')->with('status', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'rol_id' => ['required', 'exists:roles,id'],
            'nombre_usuario' => ['required', 'string', 'max:60', Rule::unique('usuarios', 'nombre_usuario')->ignore($user->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($user->id)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $payload = [
            'rol_id' => $data['rol_id'],
            'nombre_usuario' => $data['nombre_usuario'],
            'email' => $data['email'],
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
        ];

        if (! empty($data['password'])) {
            $payload['password_hash'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return redirect('/pad/usuarios')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->update(['activo' => false]);

        return redirect('/pad/usuarios')->with('status', 'Usuario desactivado correctamente.');
    }
}
