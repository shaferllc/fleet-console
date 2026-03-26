<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FleetAuthConsoleDefaultsTest extends TestCase
{
    public function test_fleet_idp_web_defaults_to_session_mode_for_console(): void
    {
        $this->assertSame('session', config('fleet_idp.web.mode'));
    }

    public function test_fleet_idp_redirect_path_defaults_to_auth_callback(): void
    {
        $this->assertSame('/auth/callback', config('fleet_idp.redirect_path'));
    }

    public function test_fleet_idp_web_middleware_includes_trusted_ip_by_default(): void
    {
        $this->assertSame(['web', 'fleet.trusted_ip'], config('fleet_idp.web.middleware'));
    }

    public function test_oauth_try_again_route_points_at_console_login(): void
    {
        $this->assertSame('console.login', config('fleet_idp.web.eloquent.try_again_route'));
    }

    public function test_login_page_renders_when_fleet_idp_is_not_configured(): void
    {
        config([
            'fleet_idp.url' => '',
            'fleet_idp.client_id' => '',
            'fleet_idp.client_secret' => '',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Fleet console', false);
    }

    public function test_login_page_shows_email_field_when_password_grant_is_configured(): void
    {
        config([
            'fleet_idp.url' => 'https://fleet-auth.test',
            'fleet_idp.client_id' => 'x',
            'fleet_idp.client_secret' => 'y',
            'fleet_idp.password_client_id' => 'pw-id',
            'fleet_idp.password_client_secret' => 'pw-secret',
            'fleet_idp.user_model' => User::class,
        ]);

        $this->get(route('console.login'))
            ->assertOk()
            ->assertSee('name="email"', false);
    }

    public function test_login_page_shows_email_field_for_shared_console_password_only(): void
    {
        config([
            'fleet_idp.url' => '',
            'fleet_idp.client_id' => '',
            'fleet_idp.client_secret' => '',
            'fleet_idp.password_client_id' => '',
            'fleet_idp.password_client_secret' => '',
            'fleet_console.password' => 'shared-secret',
            'fleet_console.password_hash' => '',
        ]);

        $this->get(route('console.login'))
            ->assertOk()
            ->assertSee('name="email"', false);
    }

    public function test_oauth_routes_are_registered(): void
    {
        $this->get('/oauth/fleet-auth')
            ->assertRedirect(route('console.login', absolute: false));

        $this->get('/auth/callback')
            ->assertRedirect(route('console.login', absolute: false));
    }
}
