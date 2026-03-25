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

        $expected = config('fleet_console.password');
        if (! is_string($expected) || $expected === '') {
            abort(503, 'Fleet console is not configured.');
        }

        if (! hash_equals(hash('sha256', $expected), hash('sha256', $request->input('password')))) {
            throw ValidationException::withMessages([
                'password' => __('Invalid password.'),
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
