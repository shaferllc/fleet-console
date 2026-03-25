@if ($total > 0 || ($fleet_samples_24h ?? 0) > 0 || ($fleet_samples_7d ?? 0) > 0)
    <div id="fleet-stats-strip" class="mt-10 space-y-3">
        @if ($total > 0)
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="fc-glass rounded-2xl px-5 py-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Targets</p>
                    <p class="fc-stat-pill mt-1 text-2xl font-semibold tabular-nums text-white">{{ $total }}</p>
                </div>
                <div class="fc-glass rounded-2xl px-5 py-4 ring-1 ring-emerald-500/15">
                    <p class="text-xs font-medium uppercase tracking-wider text-emerald-500/80">Healthy</p>
                    <p class="fc-stat-pill mt-1 text-2xl font-semibold tabular-nums text-emerald-300">{{ $okCount }}</p>
                </div>
                <div class="fc-glass rounded-2xl px-5 py-4 {{ $errCount > 0 ? 'ring-1 ring-red-500/20' : '' }}">
                    <p class="text-xs font-medium uppercase tracking-wider {{ $errCount > 0 ? 'text-red-400/90' : 'text-zinc-500' }}">Errors</p>
                    <p class="fc-stat-pill mt-1 text-2xl font-semibold tabular-nums {{ $errCount > 0 ? 'text-red-300' : 'text-zinc-400' }}">{{ $errCount }}</p>
                </div>
            </div>
        @endif

        @if (($fleet_samples_24h ?? 0) > 0 || ($fleet_samples_7d ?? 0) > 0)
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="fc-glass rounded-2xl px-5 py-4 ring-1 ring-cyan-500/10">
                    <p class="text-xs font-medium uppercase tracking-wider text-cyan-500/80">Fleet polls · 24h</p>
                    @if (($fleet_ok_24h ?? null) !== null)
                        <p class="fc-stat-pill mt-1 text-2xl font-semibold tabular-nums text-cyan-200">{{ $fleet_ok_24h }}% <span class="text-base font-medium text-zinc-500">OK</span></p>
                        <p class="mt-1 text-xs text-zinc-500">{{ number_format($fleet_samples_24h) }} stored polls across targets</p>
                    @else
                        <p class="mt-2 text-sm text-zinc-500">No samples in the last 24h.</p>
                    @endif
                </div>
                <div class="fc-glass rounded-2xl px-5 py-4 ring-1 ring-cyan-500/10">
                    <p class="text-xs font-medium uppercase tracking-wider text-cyan-500/80">Fleet polls · 7d</p>
                    @if (($fleet_ok_7d ?? null) !== null)
                        <p class="fc-stat-pill mt-1 text-2xl font-semibold tabular-nums text-cyan-200/95">{{ $fleet_ok_7d }}% <span class="text-base font-medium text-zinc-500">OK</span></p>
                        <p class="mt-1 text-xs text-zinc-500">{{ number_format($fleet_samples_7d) }} stored polls across targets</p>
                    @else
                        <p class="mt-2 text-sm text-zinc-500">No samples in the last 7 days.</p>
                    @endif
                </div>
            </div>
        @elseif ($total > 0)
            <p class="rounded-xl border border-zinc-800/80 bg-zinc-950/40 px-4 py-3 text-xs text-zinc-500">
                No stored poll history yet — use <span class="font-medium text-zinc-400">Refresh</span> on a card or <span class="font-medium text-zinc-400">Refresh all</span> to build fleet-wide stats.
            </p>
        @endif
    </div>
@endif
