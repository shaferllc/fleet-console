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

        $token = $target['operator_token'] ?? null;
        $tokenMissing = ! is_string($token) || $token === '';

        $baseUrl = rtrim((string) ($target['base_url'] ?? ''), '/');
        $rawDesc = $target['description'] ?? null;
        $description = is_string($rawDesc) ? trim($rawDesc) : '';
        $rawSite = $target['site_url'] ?? null;
        $siteUrl = $baseUrl !== '' ? rtrim((is_string($rawSite) && $rawSite !== '') ? $rawSite : $baseUrl, '/') : '';

        $rawStaging = $target['staging_site_url'] ?? null;
        $stagingSiteUrl = (is_string($rawStaging) && $rawStaging !== '') ? rtrim($rawStaging, '/') : '';

        $operatorPrefix = (string) ($target['operator_path_prefix'] ?? '/api/operator');
        $operatorPrefix = '/'.ltrim(rtrim($operatorPrefix, '/'), '/');
        $url = $baseUrl.$operatorPrefix.'/readme';

        $error = null;
        $html = '';
        $raw = '';
        $readmeTitle = null;
        $readmeSubtitle = null;
        $readmeFormat = null;

        if ($tokenMissing) {
            $error = 'No operator token is stored for this service — add one under Console → Services → Edit (must match FLEET_OPERATOR_TOKEN on the target app).';
        } else {
            try {
                $response = Http::timeout(20)
                    ->withOptions(['verify' => (bool) config('fleet_console.http_verify', true)])
                    ->withToken($token)
                    ->acceptJson()
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    if (! is_array($data)) {
                        $data = [];
                    }
                    $raw = is_string($data['content'] ?? null) ? $data['content'] : '';
                    $t = $data['title'] ?? null;
                    $readmeTitle = is_string($t) && $t !== '' ? $t : null;
                    $st = $data['subtitle'] ?? null;
                    $readmeSubtitle = is_string($st) && $st !== '' ? $st : null;
                    $fmt = $data['format'] ?? null;
                    $readmeFormat = is_string($fmt) && $fmt !== '' ? $fmt : null;
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
            'stagingSiteUrl' => $stagingSiteUrl,
            'readmeUrl' => $url,
            'html' => $html,
            'rawFallback' => $raw === '' && $error === null ? 'Empty README response.' : null,
            'error' => $error,
            'readmeTitle' => $readmeTitle,
            'readmeSubtitle' => $readmeSubtitle,
            'readmeFormat' => $readmeFormat,
        ]);
    }
}
