{{-- Expects $row with sparkline, sparkline_7d, sparkline_7d_rollups, slo_24h, slo_7d, latency_*, latency_7d_* --}}
@php
    $sl24 = $row['sparkline'] ?? [];
    $sl7d = $row['sparkline_7d'] ?? [];
    $roll7 = ! empty($row['sparkline_7d_rollups']);
    $hasLat24 = ($row['latency_last'] ?? null) !== null || ($row['latency_p50'] ?? null) !== null;
    $hasLat7 = ($row['latency_7d_p50'] ?? null) !== null || (($row['latency_7d_samples'] ?? 0) > 0);
@endphp
<div class="relative mt-4">
    <div class="mb-2 flex flex-wrap items-end justify-between gap-x-3 gap-y-2">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-600">Uptime strip</p>
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px]">
            @if (($row['slo_24h'] ?? null) !== null)
                <span class="font-mono text-zinc-500" title="Share of stored polls that succeeded in the window">
                    <span class="text-emerald-400/90">{{ $row['slo_24h'] }}%</span>
                    <span class="text-zinc-600"> OK · 24h</span>
                </span>
            @endif
            @if (($row['slo_7d'] ?? null) !== null)
                <span class="font-mono text-zinc-500" title="Share of stored polls that succeeded in the window">
                    <span class="text-zinc-600">·</span>
                    <span class="text-emerald-400/85">{{ $row['slo_7d'] }}%</span>
                    <span class="text-zinc-600"> · 7d</span>
                </span>
            @endif
            <span class="ml-1 inline-flex rounded-md bg-zinc-950/60 p-0.5 ring-1 ring-zinc-800/60" role="group" aria-label="Uptime and latency time range">
                <button
                    type="button"
                    class="fc-spark-range rounded px-2 py-0.5 font-medium text-cyan-200 ring-2 ring-cyan-500/40 outline-none focus-visible:ring-2 focus-visible:ring-cyan-500/50"
                    data-spark-range="24"
                    aria-pressed="true"
                >
                    24h
                </button>
                <button
                    type="button"
                    class="fc-spark-range rounded px-2 py-0.5 font-medium text-zinc-500 transition hover:text-zinc-300 outline-none focus-visible:ring-2 focus-visible:ring-cyan-500/50"
                    data-spark-range="168"
                    aria-pressed="false"
                >
                    7d
                </button>
            </span>
        </div>
    </div>

    <div data-spark-panel="24" class="fc-spark-panel">
        @if (! empty($sl24) && is_array($sl24))
            <div
                class="flex h-7 max-w-full items-end gap-px overflow-hidden rounded-md bg-zinc-950/50 p-1 ring-1 ring-zinc-800/60"
                title="Downsampled polls: green = OK, red = error (last 24h)."
            >
                @foreach ($sl24 as $bit)
                    <span
                        class="min-h-[5px] min-w-[3px] flex-1 rounded-sm {{ $bit ? 'bg-emerald-500/85' : 'bg-red-500/85' }}"
                        style="max-width: 5px"
                    ></span>
                @endforeach
            </div>
        @else
            <p class="text-xs text-zinc-600">No samples in the last 24h — polls are stored on each load or refresh.</p>
        @endif
    </div>

    <div data-spark-panel="168" class="fc-spark-panel hidden">
        @if (! empty($sl7d) && is_array($sl7d))
            <div
                class="flex h-7 max-w-full items-end gap-px overflow-hidden rounded-md bg-zinc-950/50 p-1 ring-1 ring-zinc-800/60"
                title="{{ $roll7 ? 'High volume: one color bucket per calendar day (from daily rollups + today’s polls). Green = all OK that day.' : 'Downsampled polls: green = OK, red = error (last 7 days).' }}"
            >
                @foreach ($sl7d as $bit)
                    <span
                        class="min-h-[5px] min-w-[3px] flex-1 rounded-sm {{ $bit ? 'bg-emerald-500/85' : 'bg-red-500/85' }}"
                        style="max-width: 5px"
                    ></span>
                @endforeach
            </div>
        @else
            <p class="text-xs text-zinc-600">
                No samples in the last 7 days. Retention is controlled by
                <code class="rounded bg-zinc-900 px-1 font-mono text-[10px] text-zinc-400">fleet:prune-poll-samples --days=</code>
                (default 45). Run
                <code class="rounded bg-zinc-900 px-1 font-mono text-[10px] text-zinc-400">fleet:aggregate-poll-daily</code>
                for daily rollups after heavy polling.
            </p>
        @endif
    </div>
</div>

@if ($hasLat24 || $hasLat7)
    <div class="mt-3">
        <p class="font-sans text-[10px] font-semibold uppercase tracking-wider text-zinc-600">
            Latency
            <span class="ml-1 font-normal normal-case text-zinc-600">(matches 24h / 7d toggle)</span>
        </p>

        <div data-latency-panel="24" class="fc-latency-panel mt-1.5 font-mono text-[11px] leading-relaxed text-zinc-500">
            @if ($hasLat24)
                @if (($row['latency_last'] ?? null) !== null)
                    <span class="text-zinc-400">last {{ $row['latency_last'] }} ms</span>
                @endif
                @if (($row['latency_p50'] ?? null) !== null)
                    <span class="mx-2 text-zinc-700">·</span>
                    <span>p50 {{ $row['latency_p50'] }} ms</span>
                    <span class="mx-2 text-zinc-700">·</span>
                    <span>p95 {{ $row['latency_p95'] }} ms</span>
                    @if (($row['latency_samples'] ?? 0) > 0)
                        <span class="ml-2 text-zinc-600">({{ $row['latency_samples'] }} samples / 24h)</span>
                    @endif
                @elseif (($row['latency_last'] ?? null) === null)
                    <span class="text-zinc-600">No latency samples in the last 24h.</span>
                @endif
            @else
                <span class="text-zinc-600">No 24h latency data.</span>
            @endif
        </div>

        <div data-latency-panel="168" class="fc-latency-panel mt-1.5 hidden font-mono text-[11px] leading-relaxed text-zinc-500">
            @if ($hasLat7)
                @if (($row['latency_last'] ?? null) !== null)
                    <span class="text-zinc-400">last {{ $row['latency_last'] }} ms</span>
                @endif
                @if (($row['latency_7d_p50'] ?? null) !== null)
                    <span class="mx-2 text-zinc-700">·</span>
                    <span>p50 {{ $row['latency_7d_p50'] }} ms</span>
                    <span class="mx-2 text-zinc-700">·</span>
                    <span>p95 {{ $row['latency_7d_p95'] }} ms</span>
                    @if (($row['latency_7d_samples'] ?? 0) > 0)
                        <span class="ml-2 text-zinc-600">
                            ({{ $row['latency_7d_samples'] }} samples / 7d{{ $roll7 ? ', rollup-weighted' : '' }})
                        </span>
                    @endif
                @else
                    <span class="text-zinc-600">No 7d latency percentiles (missing latency_ms on polls).</span>
                @endif
            @else
                <span class="text-zinc-600">No 7d latency data.</span>
            @endif
        </div>
    </div>
@endif
