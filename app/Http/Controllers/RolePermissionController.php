<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRole($request);

        DB::transaction(function () use ($data): void {
            $role = Role::create(Arr::only($data, ['nombre', 'descripcion']));
            $role->menus()->sync($data['menu_ids']);
        });

        return redirect('/pad/perfiles')->with('status', 'Perfil creado correctamente.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validateRole($request, $role);

        DB::transaction(function () use ($role, $data): void {
            $role->update(Arr::only($data, ['nombre', 'descripcion']));
            $role->menus()->sync($data['menu_ids']);
        });

        return redirect('/pad/perfiles')->with('status', 'Perfil actualizado correctamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ((int) DB::table('usuarios')->where('rol_id', $role->id)->where('activo', true)->count() > 0) {
            return redirect('/pad/perfiles')->withErrors(['perfil' => 'No podes desactivar un perfil que tiene usuarios activos asignados.']);
        }

        $role->update(['activo' => false]);

        return redirect('/pad/perfiles')->with('status', 'Perfil desactivado correctamente.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'nombre')->ignore($role?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'menu_ids' => ['required', 'array', 'min:1'],
            'menu_ids.*' => ['integer', 'distinct', 'exists:menus,id'],
        ]);
    }
}
