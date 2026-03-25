@php
    $sloMin = config('fleet_console.alert_slo_min_ok_percent');
    $sloActive = is_numeric($sloMin);
    $sloMinF = $sloActive ? (float) $sloMin : null;
    $slo24 = $row['slo_24h'] ?? null;
    $belowSlo = $sloActive && $slo24 !== null && (float) $slo24 < $sloMinF;
    $lastTs = ! empty($row['last_poll_at']) ? \Illuminate\Support\Carbon::parse($row['last_poll_at'])->timestamp : 0;
@endphp
<tr
    class="fc-compare-row leading-tight transition hover:bg-zinc-900/40"
    data-compare-key="{{ $row['key'] }}"
    data-name="{{ strtolower($row['name'] ?? '') }}"
    data-live="{{ ! empty($row['ok']) ? '1' : '0' }}"
    data-slo24="{{ $slo24 !== null ? (float) $slo24 : -1 }}"
    data-slo7="{{ ($row['slo_7d'] ?? null) !== null ? (float) $row['slo_7d'] : -1 }}"
    data-p50="{{ ($row['latency_p50'] ?? null) !== null ? (int) $row['latency_p50'] : -1 }}"
    data-last="{{ $lastTs }}"
>
    <th scope="row" class="min-w-[7rem] max-w-[11rem] px-2.5 py-1.5 text-left sm:max-w-[14rem]">
        <p class="truncate text-xs font-medium leading-snug text-white" title="{{ ($row['name'] ?? $row['key']).' · '.$row['key'] }}">
            {{ $row['name'] ?? $row['key'] }}<span class="font-mono text-[10px] font-normal text-zinc-500"> · {{ $row['key'] }}</span>
        </p>
    </th>
    <td class="px-2.5 py-1.5">
        @if (! empty($row['ok']))
            <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-1.5 py-px text-[10px] font-semibold text-emerald-300 ring-1 ring-emerald-500/20">OK</span>
        @else
            <span class="inline-flex items-center rounded-full bg-red-500/10 px-1.5 py-px text-[10px] font-semibold text-red-300 ring-1 ring-red-500/20">Error</span>
        @endif
    </td>
    <td class="px-2.5 py-1.5 text-right font-mono text-[11px] tabular-nums">
        @if ($slo24 !== null)
            <span class="{{ $belowSlo ? 'text-amber-300' : 'text-zinc-300' }}">{{ $slo24 }}%</span>
            @if ($belowSlo)
                <span class="ml-0.5 align-top text-[9px] font-sans font-normal uppercase tracking-wide text-amber-500/90">SLO</span>
            @endif
        @else
            <span class="text-zinc-600">—</span>
        @endif
    </td>
    <td class="px-2.5 py-1.5 text-right font-mono text-[11px] tabular-nums text-zinc-400">
        {{ ($row['slo_7d'] ?? null) !== null ? $row['slo_7d'].'%' : '—' }}
    </td>
    <td class="px-2.5 py-1.5 text-right font-mono text-[11px] tabular-nums text-zinc-400">
        {{ ($row['latency_p50'] ?? null) !== null ? $row['latency_p50'] : '—' }}
    </td>
    <td class="px-2.5 py-1.5 font-mono text-[11px] text-zinc-500">
        @if (! empty($row['last_poll_at']))
            {{ \Illuminate\Support\Carbon::parse($row['last_poll_at'])->diffForHumans() }}
        @else
            —
        @endif
    </td>
</tr>
