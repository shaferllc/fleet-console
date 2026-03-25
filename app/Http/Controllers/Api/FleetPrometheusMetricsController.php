<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FleetAlertEvent;
use App\Services\FleetSummaryBuilder;
use Illuminate\Http\Response;

class FleetPrometheusMetricsController extends Controller
{
    public function __invoke(FleetSummaryBuilder $builder): Response
    {
        $data = $builder->build();
        $lines = [
            '# HELP fleet_target_up 1 if latest stored poll for target succeeded.',
            '# TYPE fleet_target_up gauge',
            '# HELP fleet_target_slo_ok_percent_24h Rolling 24h OK% from stored polls.',
            '# TYPE fleet_target_slo_ok_percent_24h gauge',
            '# HELP fleet_target_slo_ok_percent_7d Rolling 7d OK% from stored polls.',
            '# TYPE fleet_target_slo_ok_percent_7d gauge',
            '# HELP fleet_target_latency_p50_ms_24h p50 latency ms (24h window).',
            '# TYPE fleet_target_latency_p50_ms_24h gauge',
        ];

        foreach ($data['targets'] as $t) {
            $k = $this->promLabel((string) $t['key']);
            $up = isset($t['latest_poll']['ok']) && $t['latest_poll']['ok'] ? 1 : 0;
            $lines[] = 'fleet_target_up{target="'.$k.'"} '.$up;

            $s24 = $t['slo']['ok_percent_24h'] ?? null;
            if ($s24 !== null) {
                $lines[] = 'fleet_target_slo_ok_percent_24h{target="'.$k.'"} '.(float) $s24;
            }
            $s7 = $t['slo']['ok_percent_7d'] ?? null;
            if ($s7 !== null) {
                $lines[] = 'fleet_target_slo_ok_percent_7d{target="'.$k.'"} '.(float) $s7;
            }
            $p50 = $t['latency']['p50_ms_24h'] ?? null;
            if ($p50 !== null) {
                $lines[] = 'fleet_target_latency_p50_ms_24h{target="'.$k.'"} '.(int) $p50;
            }
        }

        $lines[] = '# HELP fleet_polls_total_24h Stored poll rows in last 24h (all targets).';
        $lines[] = '# TYPE fleet_polls_total_24h gauge';
        $lines[] = 'fleet_polls_total_24h '.(int) ($data['fleet']['polls_24h'] ?? 0);

        $lines[] = '# HELP fleet_alert_events_24h Alert audit rows created in the last 24h.';
        $lines[] = '# TYPE fleet_alert_events_24h gauge';
        $lines[] = 'fleet_alert_events_24h '.(int) FleetAlertEvent::query()
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $okPct = $data['fleet']['ok_percent_24h'] ?? null;
        if ($okPct !== null) {
            $lines[] = '# HELP fleet_slo_ok_percent_24h_all Weighted OK% all targets 24h.';
            $lines[] = '# TYPE fleet_slo_ok_percent_24h_all gauge';
            $lines[] = 'fleet_slo_ok_percent_24h_all '.(float) $okPct;
        }

        $body = implode("\n", $lines)."\n";

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8; version=0.0.4',
        ]);
    }

    private function promLabel(string $key): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '', ' '], $key);
    }
}
