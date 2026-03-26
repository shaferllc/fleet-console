<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFleetOperationalSettingsRequest extends FormRequest
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
            'password_hash' => ['nullable', 'string', 'max:512'],
            'http_verify' => ['sometimes', 'boolean'],
            'daily_rollup_sparkline_after_samples' => ['required', 'integer', 'min:0', 'max:10000000'],
            'api_token' => ['nullable', 'string', 'max:8192'],
            'trusted_ips' => ['nullable', 'string', 'max:16000'],
            'health_token' => ['nullable', 'string', 'max:512'],
            'background_poll_enabled' => ['sometimes', 'boolean'],
            'poll_interval_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'clear_api_token' => ['sometimes', 'boolean'],
            'clear_health_token' => ['sometimes', 'boolean'],
        ];
    }
}
