<?php

declare(strict_types=1);

namespace Dply\FleetOperator\Tests;

use Dply\FleetOperator\Http\Middleware\AuthenticateFleetOperator;
use Illuminate\Http\Request;

final class AuthenticateFleetOperatorTest extends TestCase
{
    public function test_returns_404_when_token_not_configured(): void
    {
        $this->app['config']->set('fleet_operator.token', null);

        $middleware = new AuthenticateFleetOperator;
        $request = Request::create('/summary', 'GET');
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_returns_404_when_token_is_empty_string(): void
    {
        $this->app['config']->set('fleet_operator.token', '');

        $middleware = new AuthenticateFleetOperator;
        $request = Request::create('/summary', 'GET');
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_returns_401_when_bearer_missing(): void
    {
        $this->app['config']->set('fleet_operator.token', 'secret-token');

        $middleware = new AuthenticateFleetOperator;
        $request = Request::create('/summary', 'GET');
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_returns_401_when_bearer_invalid(): void
    {
        $this->app['config']->set('fleet_operator.token', 'secret-token');

        $middleware = new AuthenticateFleetOperator;
        $request = Request::create('/summary', 'GET');
        $request->headers->set('Authorization', 'Bearer wrong');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_passes_when_bearer_matches(): void
    {
        $this->app['config']->set('fleet_operator.token', 'secret-token');

        $middleware = new AuthenticateFleetOperator;
        $request = Request::create('/summary', 'GET');
        $request->headers->set('Authorization', 'Bearer secret-token');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"ok":true}', $response->getContent());
    }
}
