<div class="overflow-x-auto rounded-xl border border-zinc-800/80 bg-zinc-950/40 ring-1 ring-zinc-800/50">
    <table class="w-full min-w-[560px] border-collapse text-left text-xs" id="fleet-compare-table">
        <caption class="sr-only">Fleet targets: live status, SLO, latency, last poll</caption>
        <thead>
            <tr class="border-b border-zinc-800 bg-zinc-950/80 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                <th scope="col" class="px-2.5 py-1.5">
                    <button type="button" class="fc-compare-sort -ml-1 rounded px-0.5 py-0 text-left font-semibold text-zinc-400 outline-none hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40" data-sort="name" data-dir="asc">
                        Target
                    </button>
                </th>
                <th scope="col" class="px-2.5 py-1.5">
                    <button type="button" class="fc-compare-sort -ml-1 rounded px-0.5 py-0 font-semibold text-zinc-400 outline-none hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40" data-sort="live" data-dir="asc">
                        Live
                    </button>
                </th>
                <th scope="col" class="px-2.5 py-1.5 text-right">
                    <button type="button" class="fc-compare-sort ml-auto block rounded px-0.5 py-0 font-semibold text-zinc-400 outline-none hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40" data-sort="slo24" data-dir="asc">
                        24h OK%
                    </button>
                </th>
                <th scope="col" class="px-2.5 py-1.5 text-right">
                    <button type="button" class="fc-compare-sort ml-auto block rounded px-0.5 py-0 font-semibold text-zinc-400 outline-none hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40" data-sort="slo7" data-dir="asc">
                        7d OK%
                    </button>
                </th>
                <th scope="col" class="px-2.5 py-1.5 text-right">
                    <button type="button" class="fc-compare-sort ml-auto block rounded px-0.5 py-0 font-semibold text-zinc-400 outline-none hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40" data-sort="p50" data-dir="asc">
                        p50 ms
                    </button>
                </th>
                <th scope="col" class="px-2.5 py-1.5">
                    <button type="button" class="fc-compare-sort -ml-1 rounded px-0.5 py-0 font-semibold text-zinc-400 outline-none hover:text-cyan-200 focus-visible:ring-2 focus-visible:ring-cyan-500/40" data-sort="last" data-dir="asc">
                        Last poll
                    </button>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-800/80 text-zinc-300">
            @foreach ($rows as $row)
                @include('console.partials.fleet-compare-row', ['row' => $row])
            @endforeach
        </tbody>
    </table>
</div>
