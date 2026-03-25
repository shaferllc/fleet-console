<li data-fleet-card="{{ $row['key'] }}" class="fc-card group relative overflow-hidden rounded-2xl p-6 sm:p-7">
    <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-cyan-500/5 blur-3xl transition-opacity group-hover:opacity-100" aria-hidden="true"></div>

    <div class="relative flex flex-wrap items-start justify-between gap-4">
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
                <a href="{{ route('console.project.readme', ['key' => $row['key']]) }}" class="inline-flex items-center rounded-lg px-3 py-1.5 font-medium text-zinc-400 ring-1 ring-zinc-700/80 transition hover:bg-zinc-800/50 hover:text-zinc-100">
                    README
                </a>
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
        <dl class="relative mt-5 grid gap-4 text-sm sm:grid-cols-2">
            @if (isset($row['summary']['users']))
                <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
                    <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Users</dt>
                    <dd class="mt-1 font-mono text-lg text-white">{{ $row['summary']['users'] }}</dd>
                </div>
            @endif
            @if (array_key_exists('organizations', $row['summary']) && $row['summary']['organizations'] !== null)
                <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
                    <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Organizations</dt>
                    <dd class="mt-1 font-mono text-lg text-white">{{ $row['summary']['organizations'] }}</dd>
                </div>
            @endif
            @if (isset($row['summary']['environment']))
                <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
                    <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Environment</dt>
                    <dd class="mt-1 font-mono text-sm text-cyan-200/90">{{ $row['summary']['environment'] }}</dd>
                </div>
            @endif
            @if (isset($row['summary']['generated_at']))
                <div class="sm:col-span-2">
                    <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Generated</dt>
                        <dd class="mt-1 font-mono text-xs text-zinc-300">{{ $row['summary']['generated_at'] }}</dd>
                    </div>
                </div>
            @endif
        </dl>
        @if (isset($row['summary']['metrics']) && is_array($row['summary']['metrics']))
            <div class="relative mt-5 border-t border-zinc-800/80 pt-5">
                <h3 class="text-[11px] font-bold uppercase tracking-[0.18em] text-zinc-500">Metrics</h3>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    @foreach ($row['summary']['metrics'] as $metric => $value)
                        <div class="flex items-baseline justify-between gap-3 rounded-lg bg-zinc-950/25 px-3 py-2 ring-1 ring-zinc-800/50">
                            <dt class="text-zinc-500">{{ str_replace('_', ' ', $metric) }}</dt>
                            <dd class="font-mono text-zinc-100">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    @endif
</li>
