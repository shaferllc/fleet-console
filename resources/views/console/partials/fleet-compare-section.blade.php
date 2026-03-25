<section id="fleet-compare-section" class="mt-14" aria-labelledby="fleet-compare-heading">
    <div class="mb-4 max-w-3xl">
        <h2 id="fleet-compare-heading" class="fc-heading text-xl font-semibold tracking-tight text-white">Fleet comparison</h2>
        <p class="mt-1 text-sm leading-relaxed text-zinc-500">
            Sort columns to scan all targets. “Below SLO” appears when
            <code class="rounded bg-zinc-900 px-1 font-mono text-[11px] text-zinc-400">FLEET_ALERT_SLO_MIN_OK_PERCENT</code>
            is set and 24h OK% is lower.
        </p>
    </div>
    @include('console.partials.fleet-compare-table', ['rows' => $rows])
</section>
