<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFleetTargetRequest extends FormRequest
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
        return [
            'key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9-]+$/', Rule::unique('fleet_targets', 'key')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:20000'],
            'base_url' => ['required', 'string', 'max:512'],
            'site_url' => ['nullable', 'string', 'max:512'],
            'staging_site_url' => ['nullable', 'string', 'max:512'],
            'operator_path_prefix' => ['nullable', 'string', 'max:128'],
            'operator_token' => ['required', 'string', 'min:8', 'max:8192'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'is_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
