<section id="fleet-compare-section" class="mt-8" aria-labelledby="fleet-compare-heading">
    <div class="mb-2 max-w-3xl">
        <h2 id="fleet-compare-heading" class="fc-heading text-lg font-semibold tracking-tight text-white">Fleet comparison</h2>
        <p class="mt-0.5 text-xs leading-snug text-zinc-500">
            Sort columns. “Below SLO” when
            <code class="rounded bg-zinc-900 px-1 font-mono text-[10px] text-zinc-400">FLEET_ALERT_SLO_MIN_OK_PERCENT</code>
            is set and 24h OK% is under that threshold.
        </p>
    </div>
    @include('console.partials.fleet-compare-table', ['rows' => $rows])
</section>
