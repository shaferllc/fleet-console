<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFleetConsoleSettingsRequest extends FormRequest
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
            'alert_email' => ['nullable', 'string', 'email', 'max:255'],
            'alert_slack_webhook' => ['nullable', 'string', 'max:2048'],
            'alert_on_recovery' => ['sometimes', 'boolean'],
            'alert_slo_min_ok_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'alert_slo_dedupe_hours' => ['required', 'integer', 'min:1', 'max:8760'],
            'alert_metric_rules_json' => ['nullable', 'string', 'max:64000'],
            'alert_webhook_urls_json' => ['nullable', 'string', 'max:64000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->assertJsonObject('alert_metric_rules_json', $validator);
            $this->assertJsonUrlArray('alert_webhook_urls_json', $validator);
        });
    }

    private function assertJsonObject(string $field, Validator $validator): void
    {
        $raw = $this->input($field);
        if (! is_string($raw) || trim($raw) === '') {
            return;
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            $validator->errors()->add($field, __('Must be a valid JSON object.'));
        }
    }

    private function assertJsonUrlArray(string $field, Validator $validator): void
    {
        $raw = $this->input($field);
        if (! is_string($raw) || trim($raw) === '') {
            return;
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            $validator->errors()->add($field, __('Must be a valid JSON array.'));

            return;
        }
        foreach ($decoded as $i => $item) {
            if (! is_string($item) || ! filter_var($item, FILTER_VALIDATE_URL)) {
                $validator->errors()->add($field, __('Entry :i must be a valid URL string.', ['i' => $i]));

                return;
            }
        }
    }
}
