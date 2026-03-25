<div
    id="fleet-detail-modal"
    class="fixed inset-0 z-[100] hidden items-start justify-center overflow-y-auto bg-black/55 px-4 py-10 backdrop-blur-sm sm:py-16"
    aria-hidden="true"
    aria-busy="false"
    role="dialog"
    aria-modal="true"
    aria-labelledby="fleet-detail-title"
    aria-describedby="fleet-detail-subtitle"
>
    <div class="absolute inset-0" data-fleet-detail-close aria-hidden="true"></div>
    <div
        id="fleet-detail-panel"
        class="relative z-10 w-full max-w-3xl rounded-2xl border border-zinc-800/80 bg-zinc-950/95 p-5 shadow-2xl shadow-black/40 ring-1 ring-zinc-800/60 outline-none focus-visible:ring-2 focus-visible:ring-cyan-500/35 sm:p-6"
        tabindex="-1"
    >
        <div class="flex items-start justify-between gap-4 border-b border-zinc-800/80 pb-4">
            <div class="min-w-0">
                <h2 id="fleet-detail-title" class="fc-heading truncate text-lg font-semibold text-white">Poll detail</h2>
                <p id="fleet-detail-subtitle" class="mt-1 font-mono text-xs text-zinc-500"></p>
            </div>
            <button
                type="button"
                id="fleet-detail-close-btn"
                class="shrink-0 rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-1.5 text-xs font-medium text-zinc-300 outline-none transition hover:border-zinc-500 hover:text-white focus-visible:ring-2 focus-visible:ring-cyan-500/40"
                data-fleet-detail-close
            >
                Close
            </button>
        </div>
        <div
            id="fleet-detail-status"
            class="sr-only"
            role="status"
            aria-live="polite"
            aria-atomic="true"
        ></div>
        <div id="fleet-detail-body" class="mt-4 max-h-[min(70vh,32rem)] overflow-y-auto pr-1 text-sm text-zinc-300 outline-none focus-visible:ring-2 focus-visible:ring-cyan-500/25 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950 rounded-md"></div>
        <p
            id="fleet-detail-error"
            class="mt-3 hidden text-sm text-red-300"
            role="alert"
            aria-live="assertive"
        ></p>
    </div>
</div>
