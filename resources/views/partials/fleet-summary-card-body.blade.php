@php
    use App\Support\FleetOperatorSummaryDisplay;

    /** @var array<string, mixed> $summary */
    $commit = FleetOperatorSummaryDisplay::shortCommit($summary['git_sha'] ?? $summary['commit'] ?? null);
    $uptime = FleetOperatorSummaryDisplay::uptimeLabel($summary['uptime_seconds'] ?? null);
    $deps = FleetOperatorSummaryDisplay::normalizedDependencies($summary);
    $links = FleetOperatorSummaryDisplay::normalizedLinks($summary);
    $runtime = $summary['runtime'] ?? $summary['php_version'] ?? null;
    $runtime = is_string($runtime) && $runtime !== '' ? $runtime : null;
    $rawVersion = $summary['version'] ?? null;
    $version = null;
    if (is_string($rawVersion) && $rawVersion !== '') {
        $version = $rawVersion;
    } elseif (is_int($rawVersion) || is_float($rawVersion)) {
        $version = (string) $rawVersion;
    }
    $appId = $summary['app'] ?? $summary['service'] ?? null;
    $appId = is_string($appId) && $appId !== '' ? $appId : null;
    $deployedAt = $summary['deployed_at'] ?? $summary['build_at'] ?? null;
    $deployedAt = is_string($deployedAt) && $deployedAt !== '' ? $deployedAt : null;
    $notes = $summary['notes'] ?? $summary['status_message'] ?? null;
    $notes = is_string($notes) && trim($notes) !== '' ? trim($notes) : null;
    $region = $summary['region'] ?? null;
    if (is_array($region)) {
        $region = implode(', ', array_filter(array_map('strval', $region)));
    }
    $region = is_string($region) && $region !== '' ? $region : null;
@endphp

@if ($notes !== null)
    <p class="relative mt-4 rounded-lg border border-cyan-500/20 bg-cyan-950/25 p-3 text-sm leading-relaxed text-cyan-100/90 ring-1 ring-cyan-500/15">
        {{ \Illuminate\Support\Str::limit($notes, 320) }}
    </p>
@endif

@if ($links !== [])
    <div class="relative mt-4 flex flex-wrap gap-2">
        @foreach ($links as $label => $url)
            <a
                href="{{ $url }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1 rounded-lg bg-zinc-900/60 px-2.5 py-1 text-[11px] font-medium text-cyan-300 ring-1 ring-zinc-700/80 transition hover:bg-zinc-800/80 hover:text-cyan-200"
            >
                {{ str_replace('_', ' ', $label) }}<span class="text-cyan-500/70" aria-hidden="true">↗</span>
            </a>
        @endforeach
    </div>
@endif

<dl class="relative mt-5 grid gap-4 text-sm sm:grid-cols-2">
    @if ($appId !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">App</dt>
            <dd class="mt-1 font-mono text-sm text-cyan-200/90">{{ $appId }}</dd>
        </div>
    @endif
    @if ($version !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Version</dt>
            <dd class="mt-1 font-mono text-lg text-white">{{ $version }}</dd>
        </div>
    @endif
    @if ($commit !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Commit</dt>
            <dd class="mt-1 font-mono text-sm text-zinc-200">{{ $commit }}</dd>
        </div>
    @endif
    @if ($runtime !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Runtime</dt>
            <dd class="mt-1 font-mono text-xs text-zinc-300">{{ $runtime }}</dd>
        </div>
    @endif
    @if ($uptime !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Uptime</dt>
            <dd class="mt-1 font-mono text-sm text-zinc-200" title="{{ (int) ($summary['uptime_seconds'] ?? 0) }} s">{{ $uptime }}</dd>
        </div>
    @endif
    @if ($region !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Region</dt>
            <dd class="mt-1 font-mono text-sm text-zinc-300">{{ $region }}</dd>
        </div>
    @endif
    @if ($deployedAt !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60 sm:col-span-2">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Deployed / build</dt>
            <dd class="mt-1 font-mono text-xs text-zinc-300">{{ $deployedAt }}</dd>
        </div>
    @endif
    @if (isset($summary['users']))
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Users</dt>
            <dd class="mt-1 font-mono text-lg text-white">{{ $summary['users'] }}</dd>
        </div>
    @endif
    @if (array_key_exists('organizations', $summary) && $summary['organizations'] !== null)
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Organizations</dt>
            <dd class="mt-1 font-mono text-lg text-white">{{ $summary['organizations'] }}</dd>
        </div>
    @endif
    @if (isset($summary['environment']))
        <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Environment</dt>
            <dd class="mt-1 font-mono text-sm text-cyan-200/90">{{ $summary['environment'] }}</dd>
        </div>
    @endif
    @if (isset($summary['generated_at']))
        <div class="sm:col-span-2">
            <div class="rounded-xl bg-zinc-950/30 p-3 ring-1 ring-zinc-800/60">
                <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500">Generated</dt>
                <dd class="mt-1 font-mono text-xs text-zinc-300">{{ $summary['generated_at'] }}</dd>
            </div>
        </div>
    @endif
</dl>

@if ($deps !== [])
    <div class="relative mt-5 border-t border-zinc-800/80 pt-5">
        <h3 class="text-[11px] font-bold uppercase tracking-[0.18em] text-zinc-500">Dependencies</h3>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach ($deps as $dep)
                <li class="flex flex-wrap items-baseline justify-between gap-2 rounded-lg bg-zinc-950/25 px-3 py-2 ring-1 ring-zinc-800/50">
                    <span class="font-medium text-zinc-300">{{ $dep['name'] }}</span>
                    <span class="inline-flex items-center gap-2">
                        @if ($dep['ok'])
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-emerald-400/90">OK</span>
                        @else
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-red-400/90">Down</span>
                        @endif
                        @if ($dep['detail'] !== null)
                            <span class="max-w-[14rem] truncate font-mono text-[10px] text-zinc-500" title="{{ $dep['detail'] }}">{{ $dep['detail'] }}</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if (isset($summary['metrics']) && is_array($summary['metrics']))
    <div class="relative mt-5 border-t border-zinc-800/80 pt-5">
        <h3 class="text-[11px] font-bold uppercase tracking-[0.18em] text-zinc-500">Metrics</h3>
        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
            @foreach ($summary['metrics'] as $metric => $value)
                <div class="flex items-baseline justify-between gap-3 rounded-lg bg-zinc-950/25 px-3 py-2 ring-1 ring-zinc-800/50">
                    <dt class="text-zinc-500">{{ str_replace('_', ' ', (string) $metric) }}</dt>
                    <dd class="font-mono text-zinc-100">{{ is_scalar($value) || $value === null ? $value : json_encode($value) }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
@endif
