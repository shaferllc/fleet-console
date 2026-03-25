@php
    $cardsTotal = count($results);
    $cardsOk = collect($results)->where('ok', true)->count();
    $cardsErr = $cardsTotal - $cardsOk;
@endphp
<div id="fleet-cards-section" class="mt-10">
    <div
        class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        data-fleet-cards-toolbar
    >
        <div class="flex flex-wrap gap-2" role="group" aria-label="Filter fleet cards by status">
            <button
                type="button"
                data-fleet-filter="all"
                aria-pressed="true"
                class="fleet-card-filter-btn rounded-lg border border-cyan-500/40 bg-cyan-950/35 px-3 py-2 text-xs font-medium text-cyan-100 ring-2 ring-cyan-500/35 transition hover:border-cyan-400/50 hover:bg-cyan-900/30"
            >
                All <span class="tabular-nums opacity-70">({{ $cardsTotal }})</span>
            </button>
            <button
                type="button"
                data-fleet-filter="ok"
                aria-pressed="false"
                class="fleet-card-filter-btn rounded-lg border border-zinc-600/50 bg-zinc-900/40 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-emerald-500/35 hover:bg-emerald-950/25 hover:text-emerald-100"
            >
                Working <span class="tabular-nums opacity-70">({{ $cardsOk }})</span>
            </button>
            <button
                type="button"
                data-fleet-filter="err"
                aria-pressed="false"
                class="fleet-card-filter-btn rounded-lg border border-zinc-600/50 bg-zinc-900/40 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-red-500/35 hover:bg-red-950/20 hover:text-red-100"
            >
                Issues <span class="tabular-nums opacity-70">({{ $cardsErr }})</span>
            </button>
        </div>
        <p class="max-w-md text-xs leading-relaxed text-zinc-500">
            <span class="text-zinc-400">Reorder:</span> use the grip on each card, then release — order is saved for the Services list and dashboard.
        </p>
    </div>
    <ul id="fleet-cards-grid" class="grid gap-5 lg:grid-cols-2" tabindex="-1">
        @foreach ($results as $row)
            @include('console.partials.fleet-target-card', ['row' => $row])
        @endforeach
    </ul>
</div>
