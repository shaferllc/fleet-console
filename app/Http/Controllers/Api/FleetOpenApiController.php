<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FleetOpenApiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Fleet Console read API',
                'version' => '1.0.0',
                'description' => 'Bearer FLEET_CONSOLE_API_TOKEN or X-Fleet-Api-Token. Uses stored polls only unless you refresh targets elsewhere.',
            ],
            'servers' => [
                ['url' => '/', 'description' => 'Same host'],
            ],
            'paths' => [
                '/api/fleet/targets' => [
                    'get' => [
                        'summary' => 'Configured targets (no secrets)',
                        'security' => [['bearerAuth' => []], ['fleetHeader' => []]],
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
                '/api/fleet/targets/{key}' => [
                    'get' => [
                        'summary' => 'Per-target poll history / SLO / sparklines (same shape as console poll-detail JSON)',
                        'parameters' => [
                            ['name' => 'key', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'pattern' => '^[a-z0-9-]+$']],
                        ],
                        'security' => [['bearerAuth' => []], ['fleetHeader' => []]],
                        'responses' => ['200' => ['description' => 'OK'], '404' => ['description' => 'Unknown target']],
                    ],
                ],
                '/api/fleet/summary' => [
                    'get' => [
                        'summary' => 'JSON fleet snapshot',
                        'parameters' => [
                            ['name' => 'since', 'in' => 'query', 'schema' => ['type' => 'string', 'format' => 'date-time'], 'description' => 'Only targets whose latest poll is on/after this instant.'],
                            ['name' => 'keys', 'in' => 'query', 'schema' => ['type' => 'string'], 'description' => 'Comma-separated target keys.'],
                        ],
                        'security' => [['bearerAuth' => []], ['fleetHeader' => []]],
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
                '/api/fleet/alerts' => [
                    'get' => [
                        'summary' => 'Recent alert dispatch audit log (newest first)',
                        'parameters' => [
                            ['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100], 'description' => 'Max rows (default 40).'],
                            ['name' => 'since', 'in' => 'query', 'schema' => ['type' => 'string', 'format' => 'date-time'], 'description' => 'Only events on or after this instant.'],
                            ['name' => 'target_key', 'in' => 'query', 'schema' => ['type' => 'string', 'maxLength' => 64], 'description' => 'Filter by target key.'],
                            ['name' => 'type', 'in' => 'query', 'schema' => ['type' => 'string', 'maxLength' => 48], 'description' => 'Filter by alert type (e.g. down, slo_breach).'],
                        ],
                        'security' => [['bearerAuth' => []], ['fleetHeader' => []]],
                        'responses' => ['200' => ['description' => 'OK'], '422' => ['description' => 'Invalid since']],
                    ],
                ],
                '/api/fleet/metrics' => [
                    'get' => [
                        'summary' => 'Prometheus exposition format',
                        'security' => [['bearerAuth' => []], ['fleetHeader' => []]],
                        'responses' => ['200' => ['description' => 'text/plain']],
                    ],
                ],
                '/api/fleet/health' => [
                    'get' => [
                        'summary' => 'DB liveness (optional FLEET_HEALTH_TOKEN)',
                        'parameters' => [
                            ['name' => 'token', 'in' => 'query', 'schema' => ['type' => 'string']],
                        ],
                        'responses' => ['200' => ['description' => 'ok'], '403' => ['description' => 'when token required'], '503' => ['description' => 'DB down']],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer'],
                    'fleetHeader' => ['type' => 'apiKey', 'in' => 'header', 'name' => 'X-Fleet-Api-Token'],
                ],
            ],
        ]);
    }
}
