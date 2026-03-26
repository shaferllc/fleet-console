<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFleetTargetAlertJson;
use App\Models\FleetTarget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFleetTargetRequest extends FormRequest
{
    use ValidatesFleetTargetAlertJson;

    public function authorize(): bool
    {
        return (bool) $this->session()->get('fleet_console_ok');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var FleetTarget $fleetTarget */
        $fleetTarget = $this->route('fleet_target');

        return [
            'key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9-]+$/', Rule::unique('fleet_targets', 'key')->ignore($fleetTarget->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'base_url' => ['required', 'string', 'max:512'],
            'site_url' => ['nullable', 'string', 'max:512'],
            'staging_site_url' => ['nullable', 'string', 'max:512'],
            'operator_path_prefix' => ['nullable', 'string', 'max:128'],
            'operator_token' => [
                'nullable',
                'string',
                'max:8192',
                Rule::when($this->filled('operator_token'), ['min:8']),
            ],
            'clear_operator_token' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'is_enabled' => ['sometimes', 'boolean'],
            'mute_alerts' => ['sometimes', 'boolean'],
            'alert_slo_min_ok_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'alert_slo_dedupe_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
            'alert_webhook_urls_json' => ['nullable', 'string', 'max:32000'],
        ];
    }
}
