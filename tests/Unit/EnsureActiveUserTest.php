<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureActiveUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EnsureActiveUserTest extends TestCase
{
    public function test_active_user_can_continue(): void
    {
        $request = Request::create('/panel', 'GET');
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
        $request->setUserResolver(fn () => new User(['activo' => true]));

        $response = (new EnsureActiveUser)->handle($request, fn () => response('panel'));

        $this->assertSame('panel', $response->getContent());
    }

    public function test_inactive_user_is_logged_out(): void
    {
        Auth::shouldReceive('logout')->once();
        $request = Request::create('/panel', 'GET');
        $session = new Store('test', new ArraySessionHandler(120));
        $session->start();
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => new User(['activo' => false]));

        $response = (new EnsureActiveUser)->handle($request, fn () => response('panel'));

        $this->assertTrue($response->isRedirect());
        $this->assertStringEndsWith('/login', $response->getTargetUrl());
    }
}
