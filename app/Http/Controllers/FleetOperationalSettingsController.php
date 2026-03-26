<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFleetOperationalSettingsRequest;
use App\Models\FleetConsoleSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FleetOperationalSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = FleetConsoleSetting::query()->firstOrFail();

        return view('console.settings.operational', [
            'settings' => $settings,
        ]);
    }

    public function update(UpdateFleetOperationalSettingsRequest $request): RedirectResponse
    {
        $settings = FleetConsoleSetting::query()->firstOrFail();
        $validated = $request->validated();

        $trusted = isset($validated['trusted_ips']) && is_string($validated['trusted_ips'])
            ? trim($validated['trusted_ips'])
            : '';

        $data = [
            'http_verify' => $request->boolean('http_verify'),
            'daily_rollup_sparkline_after_samples' => max(0, (int) $validated['daily_rollup_sparkline_after_samples']),
            'trusted_ips' => $trusted !== '' ? $trusted : null,
            'background_poll_enabled' => $request->boolean('background_poll_enabled'),
            'poll_interval_minutes' => max(1, min(120, (int) $validated['poll_interval_minutes'])),
        ];

        if ($request->filled('password_hash')) {
            $data['password_hash'] = trim((string) $validated['password_hash']);
        }

        if ($request->boolean('clear_api_token')) {
            $data['api_token'] = null;
        } elseif ($request->filled('api_token')) {
            $data['api_token'] = (string) $validated['api_token'];
        }

        if ($request->boolean('clear_health_token')) {
            $data['health_token'] = null;
        } elseif ($request->filled('health_token')) {
            $data['health_token'] = (string) $validated['health_token'];
        }

        $settings->update($data);

        return redirect()
            ->route('console.settings.operational')
            ->with('status', __('Console settings saved.'));
    }
}
