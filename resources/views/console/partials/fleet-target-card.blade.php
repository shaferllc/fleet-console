<li
    data-fleet-card="{{ $row['key'] }}"
    data-fleet-card-status="{{ ! empty($row['ok']) ? 'ok' : 'err' }}"
    data-fleet-reorderable="{{ isset($row['fleet_target_id']) ? '1' : '0' }}"
    class="fc-card group relative overflow-hidden rounded-2xl p-6 sm:p-7"
>
    <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-cyan-500/5 blur-3xl transition-opacity group-hover:opacity-100" aria-hidden="true"></div>

    <div class="relative flex flex-wrap items-start justify-between gap-4">
        @if (isset($row['fleet_target_id']))
            <button
                type="button"
                data-fleet-drag-handle
                class="touch-none mt-0.5 shrink-0 cursor-grab rounded-lg border border-zinc-700/60 bg-zinc-900/40 px-1.5 py-2 text-zinc-500 outline-none active:cursor-grabbing hover:border-zinc-500 hover:text-zinc-300 focus-visible:ring-2 focus-visible:ring-cyan-500/40"
                aria-label="Drag to reorder {{ $row['name'] }}"
            >
                <span class="block font-mono text-sm leading-none tracking-tighter text-zinc-400" aria-hidden="true">⋮⋮</span>
            </button>
        @endif
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-baseline gap-y-1">
                <a href="{{ route('console.project.readme', ['key' => $row['key']]) }}" class="fc-heading text-xl font-semibold text-white transition hover:text-cyan-200">{{ $row['name'] }}</a>
                <span class="mx-2 shrink-0 text-zinc-600 select-none sm:mx-2.5" aria-hidden="true">·</span>
                <span class="font-mono text-sm text-zinc-500">{{ $row['key'] }}</span>
            </div>
            @if (! empty($row['description']))
                <p class="mt-2.5 text-sm leading-relaxed text-zinc-400">{{ $row['description'] }}</p>
            @endif
            <div class="mt-4 flex flex-wrap items-center gap-x-1 gap-y-2 text-xs">
                @if (! empty($row['site_url']))
                    <a href="{{ $row['site_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-cyan-500/10 px-3 py-1.5 font-medium text-cyan-300 ring-1 ring-cyan-500/20 transition hover:bg-cyan-500/15 hover:text-cyan-200" aria-label="Open {{ $row['name'] }} in a new tab">
                        Open site<span class="text-cyan-500/80" aria-hidden="true">↗</span>
                    </a>
                    <span class="mx-2 text-zinc-600 select-none sm:mx-3" aria-hidden="true">·</span>
                @endif
                @if (! empty($row['staging_site_url']))
                    <a href="{{ $row['staging_site_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500/10 px-3 py-1.5 font-medium text-amber-200 ring-1 ring-amber-500/25 transition hover:bg-amber-500/15 hover:text-amber-100" aria-label="Open {{ $row['name'] }} staging in a new tab">
                        Open staging<span class="text-amber-500/75" aria-hidden="true">↗</span>
                    </a>
                    <span class="mx-2 text-zinc-600 select-none sm:mx-3" aria-hidden="true">·</span>
                @endif
                <a href="{{ route('console.project.readme', ['key' => $row['key']]) }}" class="inline-flex items-center rounded-lg px-3 py-1.5 font-medium text-zinc-400 ring-1 ring-zinc-700/80 transition hover:bg-zinc-800/50 hover:text-zinc-100">
                    README
                </a>
                @if (! empty($row['edit_url']))
                    <span class="mx-2 text-zinc-600 select-none sm:mx-3" aria-hidden="true">·</span>
                    <a href="{{ $row['edit_url'] }}" class="inline-flex items-center rounded-lg px-3 py-1.5 font-medium text-cyan-400/90 ring-1 ring-cyan-500/25 transition hover:bg-cyan-950/40 hover:text-cyan-200">
                        Edit service
                    </a>
                @endif
            </div>
        </div>
        <div class="flex shrink-0 flex-col items-end gap-2">
            @if ($row['ok'])
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-500/25">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.55)]" title="Reachable"></span>
                    OK
                </span>
            @else
                <span class="rounded-full bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-300 ring-1 ring-red-500/25">
                    Error
                </span>
            @endif
            <div class="flex flex-col items-end gap-1.5">
                <button
                    type="button"
                    class="rounded-lg border border-zinc-600/50 bg-zinc-900/40 px-2.5 py-1 text-[11px] font-medium text-zinc-400 outline-none transition hover:border-cyan-500/35 hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40"
                    data-fleet-refresh="{{ $row['key'] }}"
                >
                    Refresh
                </button>
                <button
                    type="button"
                    class="rounded-lg border border-zinc-700/60 bg-zinc-900/30 px-2.5 py-1 text-[11px] font-medium text-zinc-500 outline-none transition hover:border-zinc-500 hover:text-zinc-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40"
                    data-fleet-details="{{ $row['key'] }}"
                >
                    Details
                </button>
            </div>
        </div>
    </div>

    <div class="relative mt-5 space-y-2 rounded-xl bg-zinc-950/50 p-3 ring-1 ring-zinc-800/80">
        <p class="fc-url-pill break-all text-zinc-500">
            <span class="text-zinc-600">GET</span>
            <span class="mx-2 text-zinc-700">·</span>
            {{ $row['operator_summary_url'] ?? ($row['base_url'].'/api/operator/summary') }}
        </p>
        <p class="fc-url-pill break-all text-zinc-500">
            <span class="text-zinc-600">GET</span>
            <span class="mx-2 text-zinc-700">·</span>
            {{ $row['operator_readme_url'] ?? ($row['base_url'].'/api/operator/readme') }}
        </p>
    </div>

    @include('console.partials.fleet-sparkline-latency', ['row' => $row])

    @if (! $row['ok'])
        <p class="relative mt-4 rounded-lg bg-red-950/40 p-3 text-sm leading-relaxed text-red-200 ring-1 ring-red-500/20">
            @if ($row['status'])
                <span class="font-mono text-xs text-red-300/90">HTTP {{ $row['status'] }}</span>
                <span class="mx-2 text-red-500/50">—</span>
            @endif
            {{ \Illuminate\Support\Str::limit($row['error'] ?? 'Unknown error', 400) }}
        </p>
    @elseif (is_array($row['summary']))
        @include('partials.fleet-summary-card-body', ['summary' => $row['summary']])
    @endif
</li>
