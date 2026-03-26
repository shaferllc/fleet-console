<?php

namespace App\Http\Controllers;

use Fleet\IdpClient\FleetIdpOAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View as ViewContract;

class ConsoleAuthController extends Controller
{
    public function showLogin(Request $request): ViewContract|RedirectResponse
    {
        if ($request->session()->get('fleet_console_ok')) {
            return redirect()->route('console.dashboard');
        }

        return view('console.login', [
            'fleetIdpEnabled' => FleetIdpOAuth::isConfigured(),
            'localPasswordEnabled' => self::localPasswordConfigured(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $password = (string) $request->input('password');
        $expectedHash = config('fleet_console.password_hash');
        $expectedPlain = config('fleet_console.password');

        if (! self::localPasswordConfigured()) {
            throw ValidationException::withMessages([
                'password' => FleetIdpOAuth::isConfigured()
                    ? __('Use “Sign in with Fleet account” or configure a local console password.')
                    : __('Fleet console login is not configured (set FLEET_IDP_* or FLEET_CONSOLE_PASSWORD_HASH / FLEET_CONSOLE_PASSWORD).'),
            ]);
        }

        if (is_string($expectedHash) && $expectedHash !== '') {
            if (! password_verify($password, $expectedHash)) {
                throw ValidationException::withMessages([
                    'password' => __('Invalid password.'),
                ]);
            }
        } elseif (is_string($expectedPlain) && $expectedPlain !== '') {
            if (! hash_equals(hash('sha256', $expectedPlain), hash('sha256', $password))) {
                throw ValidationException::withMessages([
                    'password' => __('Invalid password.'),
                ]);
            }
        }

        $request->session()->put('fleet_console_ok', true);

        return redirect()->intended(route('console.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $stateKey = (string) config('fleet_idp.session_oauth_state_key', 'fleet_idp_oauth_state');
        $request->session()->forget(['fleet_console_ok', 'fleet_idp_user', $stateKey]);

        return redirect()->route('console.login');
    }

    private static function localPasswordConfigured(): bool
    {
        $expectedHash = config('fleet_console.password_hash');
        $expectedPlain = config('fleet_console.password');

        return (is_string($expectedHash) && $expectedHash !== '')
            || (is_string($expectedPlain) && $expectedPlain !== '');
    }
}
