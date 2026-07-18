<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTeacher($request);

        DB::transaction(function () use ($data): void {
            $user = $this->createTeacherUser($data);

            Teacher::create([
                'usuario_id' => $user->id,
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'especialidad' => $data['especialidad'] ?? null,
                'activo' => true,
            ]);
        });

        return redirect('/pad/profesores')->with('status', 'Profesor creado correctamente.');
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $this->validateTeacher($request, $teacher);

        DB::transaction(function () use ($teacher, $data): void {
            $user = $this->syncTeacherUser($teacher, $data);

            $teacher->update([
                'usuario_id' => $user->id,
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'especialidad' => $data['especialidad'] ?? null,
            ]);
        });

        return redirect('/pad/profesores')->with('status', 'Profesor actualizado correctamente.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        DB::transaction(function () use ($teacher): void {
            $teacher->update(['activo' => false]);

            if ($teacher->usuario_id) {
                User::query()
                    ->where('id', $teacher->usuario_id)
                    ->update(['activo' => false]);
            }
        });

        return redirect('/pad/profesores')->with('status', 'Profesor desactivado correctamente.');
    }

    private function validateTeacher(Request $request, ?Teacher $teacher = null): array
    {
        return $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('profesores', 'email')->ignore($teacher?->id),
                Rule::unique('usuarios', 'email')->ignore($teacher?->usuario_id),
            ],
            'especialidad' => ['nullable', 'string', 'max:150'],
            'nombre_usuario' => ['required', 'string', 'max:60', Rule::unique('usuarios', 'nombre_usuario')->ignore($teacher?->usuario_id)],
            'password' => [$teacher?->usuario_id ? 'nullable' : 'required', 'string', 'min:6'],
        ]);
    }

    private function createTeacherUser(array $data): User
    {
        return User::query()->create([
            'rol_id' => $this->teacherRoleId(),
            'nombre_usuario' => $data['nombre_usuario'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'activo' => true,
        ]);
    }

    private function syncTeacherUser(Teacher $teacher, array $data): User
    {
        $user = $teacher->usuario_id
            ? User::query()->findOrFail($teacher->usuario_id)
            : $this->createTeacherUser($data);

        $payload = [
            'rol_id' => $this->teacherRoleId(),
            'nombre_usuario' => $data['nombre_usuario'],
            'email' => $data['email'],
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'activo' => true,
        ];

        if (! empty($data['password'])) {
            $payload['password_hash'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return $user;
    }

    private function teacherRoleId(): int
    {
        return (int) DB::table('roles')->where('nombre', 'profesor')->value('id');
    }
}
