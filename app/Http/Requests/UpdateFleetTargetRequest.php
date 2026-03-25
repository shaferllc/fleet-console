<?php

namespace App\Http\Requests;

use App\Models\FleetTarget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFleetTargetRequest extends FormRequest
{
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
            'operator_path_prefix' => ['nullable', 'string', 'max:128'],
            'operator_token' => ['nullable', 'string', 'max:8192'],
            'clear_operator_token' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'is_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
