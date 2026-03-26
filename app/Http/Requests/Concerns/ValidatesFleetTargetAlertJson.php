<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Validator;

trait ValidatesFleetTargetAlertJson
{
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $raw = $this->input('alert_webhook_urls_json');
            if (! is_string($raw) || trim($raw) === '') {
                return;
            }
            $decoded = json_decode($raw, true);
            if (! is_array($decoded)) {
                $validator->errors()->add('alert_webhook_urls_json', __('Must be a valid JSON array.'));

                return;
            }
            foreach ($decoded as $i => $item) {
                if (! is_string($item) || ! filter_var($item, FILTER_VALIDATE_URL)) {
                    $validator->errors()->add('alert_webhook_urls_json', __('Entry :i must be a valid URL string.', ['i' => $i]));

                    return;
                }
            }
        });
    }
}
