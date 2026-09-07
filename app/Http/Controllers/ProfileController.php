<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Menu;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->load(['role', 'teacher']);

        return view('panel.profile', [
            'profileUser' => $user,
            'activeMenu' => 'profile',
            'menu' => $this->applicationMenu($user),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($user->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'biografia' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password_actual' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'telefono' => filled($data['telefono'] ?? null) ? $data['telefono'] : null,
            'ubicacion' => filled($data['ubicacion'] ?? null) ? $data['ubicacion'] : null,
            'biografia' => filled($data['biografia'] ?? null) ? $data['biografia'] : null,
        ];

        if ($request->hasFile('avatar')) {
            $directory = public_path('uploads/avatares');
            File::ensureDirectoryExists($directory);
            $extension = $request->file('avatar')->extension();
            $name = 'usuario-'.$user->id.'-'.now()->format('YmdHis').'.'.$extension;
            $request->file('avatar')->move($directory, $name);

            if ($user->avatar && str_starts_with($user->avatar, 'uploads/avatares/')) {
                File::delete($directory.'/'.basename($user->avatar));
            }

            $payload['avatar'] = 'uploads/avatares/'.$name;
        }

        if (filled($data['password'] ?? null)) {
            $payload['password_hash'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return redirect(AppUrl::route('profile.show'))->with('status', 'Perfil actualizado correctamente.');
    }

    public function updateTheme(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'tema' => ['required', 'in:light,dark'],
        ]);

        $user->update(['tema' => $data['tema']]);

        return response()->json(['tema' => $data['tema']]);
    }

    private function applicationMenu(User $user): array
    {
        $items = Menu::query()->where('activo', true)->orderBy('orden')->get();
        $allowedKeys = collect($user->allowedMenuKeys());
        $allowedParentIds = $items
            ->whereIn('clave', $allowedKeys)
            ->pluck('parent_id')
            ->filter()
            ->all();
        $items = $items
            ->filter(fn (Menu $item) => $allowedKeys->contains($item->clave) || in_array($item->id, $allowedParentIds, true))
            ->values();
        $childrenByParent = $items->whereNotNull('parent_id')->groupBy('parent_id');

        return $items
            ->whereNull('parent_id')
            ->mapWithKeys(function (Menu $item) use ($childrenByParent) {
                $children = ($childrenByParent[$item->id] ?? collect())
                    ->map(fn (Menu $child) => [
                        'key' => $child->clave,
                        'label' => $child->nombre,
                        'icon' => $child->icono,
                        'url' => $child->resolved_url,
                    ])
                    ->values()
                    ->all();

                return [$item->clave => [
                    'label' => $item->nombre,
                    'icon' => $item->icono,
                    'url' => $item->resolved_url,
                    'children' => $children,
                ]];
            })
            ->all();
    }
}
