<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFleetTargetRequest;
use App\Http\Requests\UpdateFleetTargetRequest;
use App\Models\FleetTarget;
use App\Support\FleetTargetDefaultCatalog;
use Illuminate\Http\RedirectResponse;
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
            'catalogCount' => count(FleetTargetDefaultCatalog::catalogRows()),
        ]);
    }

    public function create(): View
    {
        return view('console.targets.create');
    }

    public function store(StoreFleetTargetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $prefix = $data['operator_path_prefix'] ?? '/api/operator';
        $data['operator_path_prefix'] = '/'.ltrim(rtrim((string) $prefix, '/'), '/');
        if (empty($data['operator_token'])) {
            $data['operator_token'] = null;
        }
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
        $data = $request->validated();
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

    public function importDefaults(): RedirectResponse
    {
        $rows = FleetTargetDefaultCatalog::catalogRows();
        if ($rows === []) {
            return redirect()
                ->route('console.targets.index')
                ->with('error', 'No built-in catalog found (config/fleet_targets.php).');
        }

        $imported = 0;
        foreach ($rows as $i => $row) {
            $exists = FleetTarget::query()->where('key', $row['key'])->exists();
            if ($exists) {
                continue;
            }

            FleetTarget::query()->create([
                'key' => $row['key'],
                'name' => $row['name'],
                'description' => $row['description'] !== '' ? $row['description'] : null,
                'base_url' => $row['base_url'],
                'site_url' => $row['site_url'],
                'operator_path_prefix' => $row['operator_path_prefix'],
                'operator_token' => null,
                'sort_order' => $i * 10,
                'is_enabled' => true,
            ]);
            $imported++;
        }

        $msg = $imported > 0
            ? "Imported {$imported} new service(s). Existing keys were left unchanged."
            : 'All catalog services already exist — nothing to import.';

        return redirect()
            ->route('console.targets.index')
            ->with('status', $msg);
    }
}
