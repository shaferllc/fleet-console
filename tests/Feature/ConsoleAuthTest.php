<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejected_when_not_configured(): void
    {
        config([
            'fleet_console.password' => '',
            'fleet_console.password_hash' => '',
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->post(route('console.login'), ['password' => 'anything'])
            ->assertSessionHasErrors('password');
    }

    public function test_login_accepts_plain_password(): void
    {
        config([
            'fleet_console.password' => 'plain-secret',
            'fleet_console.password_hash' => '',
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->post(route('console.login'), ['password' => 'plain-secret'])
            ->assertRedirect(route('console.dashboard'));

        $this->assertTrue(session('fleet_console_ok'));
    }

    public function test_login_accepts_bcrypt_hash(): void
    {
        $hash = password_hash('bcrypt-secret', PASSWORD_BCRYPT);
        config([
            'fleet_console.password' => '',
            'fleet_console.password_hash' => $hash,
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->post(route('console.login'), ['password' => 'bcrypt-secret'])
            ->assertRedirect(route('console.dashboard'));

        $this->assertTrue(session('fleet_console_ok'));
    }

    public function test_hash_takes_precedence_over_plain_password(): void
    {
        $hash = password_hash('from-hash', PASSWORD_BCRYPT);
        config([
            'fleet_console.password' => 'from-plain',
            'fleet_console.password_hash' => $hash,
        ]);

        $this->withoutMiddleware(VerifyCsrfToken::class);

        $this->post(route('console.login'), ['password' => 'from-plain'])
            ->assertSessionHasErrors('password');

        $this->post(route('console.login'), ['password' => 'from-hash'])
            ->assertRedirect(route('console.dashboard'));
    }
}
