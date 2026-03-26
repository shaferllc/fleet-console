<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_email(): void
    {
        $this->fleetSettings()->update([
            'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->post(route('console.login'), ['password' => 'secret'])
            ->assertSessionHasErrors('email');
    }

    public function test_login_rejected_when_not_configured(): void
    {
        $this->fleetSettings()->update(['password_hash' => null]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->post(route('console.login'), [
            'email' => 'operator@example.com',
            'password' => 'anything',
        ])->assertSessionHasErrors('password');
    }

    public function test_login_accepts_bcrypt_hash(): void
    {
        $this->fleetSettings()->update([
            'password_hash' => password_hash('bcrypt-secret', PASSWORD_BCRYPT),
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->post(route('console.login'), [
            'email' => 'operator@example.com',
            'password' => 'bcrypt-secret',
        ])->assertRedirect(route('console.dashboard'));

        $this->assertTrue(session('fleet_console_ok'));
    }
}
