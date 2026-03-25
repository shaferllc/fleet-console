<?php

namespace App\Http\Controllers;

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

        return view('console.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $password = (string) $request->input('password');
        $expectedHash = config('fleet_console.password_hash');
        $expectedPlain = config('fleet_console.password');

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
        } else {
            throw ValidationException::withMessages([
                'password' => __('Fleet console login is not configured (set FLEET_CONSOLE_PASSWORD_HASH or FLEET_CONSOLE_PASSWORD).'),
            ]);
        }

        $request->session()->put('fleet_console_ok', true);

        return redirect()->intended(route('console.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('fleet_console_ok');

        return redirect()->route('console.login');
    }
}
