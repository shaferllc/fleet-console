<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderFleetTargetsRequest;
use App\Http\Requests\StoreFleetTargetRequest;
use App\Http\Requests\UpdateFleetTargetRequest;
use App\Models\FleetTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetTargetAdminController extends Controller
{
    public function index(): View
    {
        $targets = FleetTarget::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('console.targets.index', [
            'targets' => $targets,
        ]);
    }

    public function create(): View
    {
        return view('console.targets.create');
    }

    public function store(StoreFleetTargetRequest $request): RedirectResponse
    {
        $data = $this->mergeTargetAlertFields($request, $request->validated());
        $prefix = $data['operator_path_prefix'] ?? '/api/operator';
        $data['operator_path_prefix'] = '/'.ltrim(rtrim((string) $prefix, '/'), '/');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_enabled'] = $request->boolean('is_enabled', true);

        FleetTarget::query()->create($data);

        return redirect()
            ->route('console.targets.index')
            ->with('status', 'Service saved.');
    }

    public function edit(FleetTarget $fleet_target): View
    {
        return view('console.targets.edit', ['target' => $fleet_target]);
    }

    public function update(UpdateFleetTargetRequest $request, FleetTarget $fleet_target): RedirectResponse
    {
        $data = $this->mergeTargetAlertFields($request, $request->validated());
        unset($data['clear_operator_token']);

        $prefix = $data['operator_path_prefix'] ?? '/api/operator';
        $data['operator_path_prefix'] = '/'.ltrim(rtrim((string) $prefix, '/'), '/');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_enabled'] = $request->boolean('is_enabled', true);

        if ($request->boolean('clear_operator_token')) {
            $data['operator_token'] = null;
        } elseif (! $request->filled('operator_token')) {
            unset($data['operator_token']);
        } elseif ($request->input('operator_token') === '') {
            $data['operator_token'] = null;
        }

        $fleet_target->update($data);

        return redirect()
            ->route('console.targets.index')
            ->with('status', 'Service updated.');
    }

    public function destroy(FleetTarget $fleet_target): RedirectResponse
    {
        $fleet_target->delete();

        return redirect()
            ->route('console.targets.index')
            ->with('status', 'Service removed.');
    }

    public function reorder(ReorderFleetTargetsRequest $request): JsonResponse
    {
        $order = $request->validated('order');

        foreach ($order as $i => $key) {
            FleetTarget::query()->where('key', $key)->update([
                'sort_order' => $i * 10,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mergeTargetAlertFields(Request $request, array $data): array
    {
        unset($data['alert_webhook_urls_json']);
        $data['mute_alerts'] = $request->boolean('mute_alerts');
        $data['alert_webhook_urls'] = $this->decodeAlertWebhookUrlsJson((string) $request->input('alert_webhook_urls_json', ''));

        if (! array_key_exists('alert_slo_min_ok_percent', $data) || $data['alert_slo_min_ok_percent'] === '' || $data['alert_slo_min_ok_percent'] === null) {
            $data['alert_slo_min_ok_percent'] = null;
        }

        if (! array_key_exists('alert_slo_dedupe_hours', $data) || $data['alert_slo_dedupe_hours'] === '' || $data['alert_slo_dedupe_hours'] === null) {
            $data['alert_slo_dedupe_hours'] = null;
        }

        return $data;
    }

    /**
     * @return list<string>|null
     */
    private function decodeAlertWebhookUrlsJson(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return null;
        }

        $out = array_values(array_filter(
            $decoded,
            static fn (mixed $u): bool => is_string($u) && filter_var($u, FILTER_VALIDATE_URL)
        ));

        return $out === [] ? null : $out;
    }
}
