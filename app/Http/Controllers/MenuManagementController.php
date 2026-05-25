<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuManagementController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateMenu($request);

        Menu::create([
            ...$data,
            'activo' => true,
        ]);

        return redirect('/pad/menus')->with('status', 'Menu creado correctamente.');
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->validateMenu($request, $menu);

        $menu->update($data);

        return redirect('/pad/menus')->with('status', 'Menu actualizado correctamente.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->update(['activo' => false]);

        return redirect('/pad/menus')->with('status', 'Menu desactivado correctamente.');
    }

    private function validateMenu(Request $request, ?Menu $menu = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:menus,id', Rule::notIn([$menu?->id])],
            'clave' => [
                'required',
                'string',
                'max:50',
                Rule::unique('menus', 'clave')->ignore($menu?->id),
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'icono' => ['nullable', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:150'],
            'tablas_relacionadas' => ['nullable', 'string'],
            'orden' => ['required', 'integer', 'min:1'],
        ]);
    }
}
