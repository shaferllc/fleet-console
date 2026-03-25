<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectReadmeController extends Controller
{
    public function show(string $key): View
    {
        $targets = config('fleet_console.targets', []);
        $target = collect($targets)->firstWhere('key', $key);
        if (! is_array($target)) {
            abort(404);
        }

        $defaultToken = config('fleet_console.operator_token');
        $token = $target['operator_token'] ?? $defaultToken;
        $tokenMissing = ! is_string($token) || $token === '';

        $baseUrl = rtrim((string) ($target['base_url'] ?? ''), '/');
        $rawDesc = $target['description'] ?? null;
        $description = is_string($rawDesc) ? trim($rawDesc) : '';
        $rawSite = $target['site_url'] ?? null;
        $siteUrl = $baseUrl !== '' ? rtrim((is_string($rawSite) && $rawSite !== '') ? $rawSite : $baseUrl, '/') : '';

        $operatorPrefix = (string) ($target['operator_path_prefix'] ?? '/api/operator');
        $operatorPrefix = '/'.ltrim(rtrim($operatorPrefix, '/'), '/');
        $url = $baseUrl.$operatorPrefix.'/readme';

        $error = null;
        $html = '';
        $raw = '';

        if ($tokenMissing) {
            $error = 'Fleet is not configured to send an operator bearer token (set FLEET_OPERATOR_TOKEN on Fleet or a per-target token; must match the target app).';
        } else {
            try {
                $response = Http::timeout(20)
                    ->withOptions(['verify' => (bool) config('fleet_console.http_verify', true)])
                    ->withToken($token)
                    ->acceptJson()
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $raw = is_string($data['content'] ?? null) ? $data['content'] : '';
                    $html = $raw !== '' ? Str::markdown($raw, [
                        'allow_unsafe_links' => false,
                    ]) : '';
                } else {
                    $error = 'HTTP '.$response->status().' — '.Str::limit($response->body(), 500);
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('console.readme', [
            'target' => $target,
            'description' => $description,
            'siteUrl' => $siteUrl,
            'readmeUrl' => $url,
            'html' => $html,
            'rawFallback' => $raw === '' && $error === null ? 'Empty README response.' : null,
            'error' => $error,
        ]);
    }
}
