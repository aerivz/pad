<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    public static function rules(bool $required): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            Password::min(8)->letters()->mixedCase()->numbers()->symbols(),
            'confirmed',
        ];
    }

    public static function messages(): array
    {
        return [
            'password.required' => 'La contrasena es obligatoria.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'password.letters' => 'La contrasena debe incluir letras.',
            'password.mixed' => 'La contrasena debe incluir mayusculas y minusculas.',
            'password.numbers' => 'La contrasena debe incluir al menos un numero.',
            'password.symbols' => 'La contrasena debe incluir al menos un simbolo.',
            'password.confirmed' => 'La confirmacion de la contrasena no coincide.',
        ];
    }

    public static function isStrong(string $password): bool
    {
        return mb_strlen($password) >= 8
            && preg_match('/\p{Ll}/u', $password) === 1
            && preg_match('/\p{Lu}/u', $password) === 1
            && preg_match('/\pN/u', $password) === 1
            && preg_match('/\p{Z}|\p{S}|\p{P}/u', $password) === 1;
    }
}
