<section id="fleet-compare-section" class="mt-8" aria-labelledby="fleet-compare-heading">
    <div class="mb-2 max-w-3xl">
        <h2 id="fleet-compare-heading" class="fc-heading text-lg font-semibold tracking-tight text-white">Fleet comparison</h2>
        <p class="mt-0.5 text-xs leading-snug text-zinc-500">
            Sort columns. “Below SLO” when a minimum 24h OK% is configured under
            <a href="{{ route('console.settings.alerts') }}" class="font-medium text-cyan-400/90 underline decoration-cyan-500/35 underline-offset-2 hover:text-cyan-300">Alert settings</a>
            (or per-service) and 24h OK% is under that threshold.
        </p>
    </div>
    @include('console.partials.fleet-compare-table', ['rows' => $rows])
</section>
