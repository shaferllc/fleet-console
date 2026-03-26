<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFleetConsoleSettingsRequest;
use App\Models\FleetConsoleSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FleetConsoleSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = FleetConsoleSetting::query()->firstOrFail();

        return view('console.settings.alerts', [
            'settings' => $settings,
            'metricRulesJson' => json_encode(
                $settings->alert_metric_rules ?? [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ) ?: '{}',
            'webhookUrlsJson' => json_encode(
                $settings->alert_webhook_urls ?? [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ) ?: '[]',
        ]);
    }

    public function update(UpdateFleetConsoleSettingsRequest $request): RedirectResponse
    {
        $settings = FleetConsoleSetting::query()->firstOrFail();
        $validated = $request->validated();

        $metricRules = [];
        $rawMetric = $validated['alert_metric_rules_json'] ?? '';
        if (is_string($rawMetric) && trim($rawMetric) !== '') {
            $metricRules = json_decode($rawMetric, true);
            if (! is_array($metricRules)) {
                $metricRules = [];
            }
        }

        $webhooks = [];
        $rawHooks = $validated['alert_webhook_urls_json'] ?? '';
        if (is_string($rawHooks) && trim($rawHooks) !== '') {
            $webhooks = json_decode($rawHooks, true);
            if (! is_array($webhooks)) {
                $webhooks = [];
            }
            $webhooks = array_values(array_filter(
                $webhooks,
                static fn (mixed $u): bool => is_string($u) && filter_var($u, FILTER_VALIDATE_URL)
            ));
        }

        $email = $validated['alert_email'] ?? null;
        $slack = $validated['alert_slack_webhook'] ?? null;

        $settings->update([
            'alert_email' => is_string($email) && $email !== '' ? $email : null,
            'alert_slack_webhook' => is_string($slack) && $slack !== '' ? $slack : null,
            'alert_on_recovery' => $request->boolean('alert_on_recovery'),
            'alert_metric_rules' => $metricRules,
            'alert_slo_min_ok_percent' => isset($validated['alert_slo_min_ok_percent']) && $validated['alert_slo_min_ok_percent'] !== null && $validated['alert_slo_min_ok_percent'] !== ''
                ? (float) $validated['alert_slo_min_ok_percent']
                : null,
            'alert_slo_dedupe_hours' => max(1, (int) $validated['alert_slo_dedupe_hours']),
            'alert_webhook_urls' => $webhooks,
        ]);

        return redirect()
            ->route('console.settings.alerts')
            ->with('status', __('Alert settings saved.'));
    }
}
