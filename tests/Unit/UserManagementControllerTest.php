<?php

namespace Tests\Unit;

use App\Support\PasswordPolicy;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserManagementControllerTest extends TestCase
{
    public function test_new_user_rejects_a_weak_password(): void
    {
        $validator = Validator::make(
            ['password' => 'abcdef12', 'password_confirmation' => 'abcdef12'],
            ['password' => PasswordPolicy::rules(required: true)]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_new_user_accepts_a_strong_confirmed_password(): void
    {
        $validator = Validator::make(
            ['password' => 'Clave-Segura9', 'password_confirmation' => 'Clave-Segura9'],
            ['password' => PasswordPolicy::rules(required: true)]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_password_confirmation_must_match(): void
    {
        $validator = Validator::make(
            ['password' => 'Clave-Segura9', 'password_confirmation' => 'Otra-Clave9'],
            ['password' => PasswordPolicy::rules(required: true)]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_existing_weak_password_is_detected_at_login(): void
    {
        $this->assertFalse(PasswordPolicy::isStrong('123456'));
        $this->assertFalse(PasswordPolicy::isStrong('password'));
        $this->assertTrue(PasswordPolicy::isStrong('Clave-Segura9'));
    }
}
