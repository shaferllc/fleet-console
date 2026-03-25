@extends('layouts.console')

@section('title', 'Dashboard')

@section('topbar_actions')
    <button
        type="button"
        id="fleet-refresh-all"
        class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-cyan-500/35 hover:bg-zinc-800/70 hover:text-cyan-100 disabled:opacity-40"
    >
        Refresh all
    </button>
    <form method="post" action="{{ route('console.logout') }}" class="inline">
        @csrf
        <button type="submit" class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70 hover:text-white">
            Sign out
        </button>
    </form>
@endsection

@section('content')
    @if ($total > 0)
        <a
            href="#fleet-cards-grid"
            id="fleet-skip-to-cards"
            class="fixed left-4 top-4 z-[120] -translate-y-[180%] rounded-lg border border-cyan-500/40 bg-zinc-950/95 px-4 py-2 text-sm font-medium text-cyan-100 shadow-lg outline-none transition focus:translate-y-0 focus:ring-2 focus:ring-cyan-500/45"
        >
            Skip to fleet cards
        </a>
    @endif

    <header class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-500/90">Live fleet</p>
        <h1 class="fc-heading mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Overview</h1>
        <p class="mt-3 text-base leading-relaxed text-zinc-400">
            Health and counts from each app’s operator API — history, latency, and alerts build as you poll (page load or refresh).
        </p>
    </header>

    @include('console.partials.fleet-stats-strip', [
        'total' => $total,
        'okCount' => $okCount,
        'errCount' => $errCount,
        'fleet_ok_24h' => $fleet_ok_24h,
        'fleet_ok_7d' => $fleet_ok_7d,
        'fleet_samples_24h' => $fleet_samples_24h,
        'fleet_samples_7d' => $fleet_samples_7d,
    ])

    @include('console.partials.fleet-alert-timeline', ['alertEvents' => $alertEvents])

    @if (count($results) === 0)
        <div class="fc-glass mt-10 rounded-2xl border-amber-500/20 p-6 ring-1 ring-amber-500/10">
            <p class="text-sm leading-relaxed text-amber-100/90">
                No targets configured. Add apps in
                <code class="rounded-md bg-zinc-950/80 px-1.5 py-0.5 font-mono text-xs text-amber-200/90">config/fleet_targets.php</code>
                or set a non-empty
                <code class="rounded-md bg-zinc-950/80 px-1.5 py-0.5 font-mono text-xs text-amber-200/90">FLEET_CONSOLE_TARGETS</code>
                JSON in <code class="rounded-md bg-zinc-950/80 px-1.5 py-0.5 font-mono text-xs text-amber-200/90">.env</code>.
            </p>
        </div>
    @else
        @push('fleet_overlays')
            @include('console.partials.fleet-detail-modal')
        @endpush
        @include('console.partials.fleet-compare-section', ['rows' => $results])
        @include('console.partials.fleet-cards-grid', ['results' => $results])
    @endif
@endsection
