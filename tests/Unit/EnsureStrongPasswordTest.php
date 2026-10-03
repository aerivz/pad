<?php

namespace Tests\Unit;

use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureStrongPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class EnsureStrongPasswordTest extends TestCase
{
    public function test_user_with_weak_password_is_redirected_to_profile(): void
    {
        $request = $this->requestFor(PanelController::class.'@dashboard');

        $response = (new EnsureStrongPassword)->handle($request, fn () => response('panel'));

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/perfil', $response->getTargetUrl());
    }

    public function test_user_can_open_profile_to_change_password(): void
    {
        $request = $this->requestFor(ProfileController::class.'@show');

        $response = (new EnsureStrongPassword)->handle($request, fn () => response('perfil'));

        $this->assertSame('perfil', $response->getContent());
    }

    private function requestFor(string $action): Request
    {
        $request = Request::create('/prueba', 'GET');
        $session = new Store('test', new ArraySessionHandler(120));
        $session->start();
        $session->put('password_change_required', true);
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => new User);

        $request->setRouteResolver(fn () => new class($action)
        {
            public function __construct(private readonly string $action) {}

            public function getActionName(): string
            {
                return $this->action;
            }
        });

        return $request;
    }
}
