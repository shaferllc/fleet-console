<section
    id="fleet-alert-timeline-section"
    class="fc-glass mt-10 rounded-2xl border border-zinc-700/40 p-5 ring-1 ring-zinc-800/60"
    aria-labelledby="fleet-alert-timeline-heading"
>
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <h2 id="fleet-alert-timeline-heading" class="text-sm font-semibold uppercase tracking-wider text-cyan-500/85">
            Alert timeline
        </h2>
        <p class="text-xs text-zinc-500">Recent dispatches (email, Slack, webhooks)</p>
    </div>

    @if ($alertEvents->isEmpty())
        <p class="mt-4 text-sm text-zinc-500">No alert events stored yet.</p>
    @else
        <ul class="mt-4 divide-y divide-zinc-800/80 text-sm">
            @foreach ($alertEvents as $ev)
                <li class="flex flex-wrap gap-x-4 gap-y-1 py-3 first:pt-0">
                    <time
                        class="shrink-0 font-mono text-xs text-zinc-500"
                        datetime="{{ $ev->created_at?->toIso8601String() }}"
                    >
                        {{ $ev->created_at?->timezone(config('app.timezone'))->format('M j, H:i') }}
                    </time>
                    <span class="font-mono text-xs text-amber-200/90">{{ $ev->type }}</span>
                    @if ($ev->target_key)
                        <span class="font-mono text-xs text-zinc-400">{{ $ev->target_key }}</span>
                    @endif
                    @if ($ev->channels)
                        <span class="text-xs text-zinc-500">{{ $ev->channels }}</span>
                    @endif
                    <span class="min-w-0 basis-full text-zinc-300 sm:basis-auto">{{ \Illuminate\Support\Str::limit($ev->subject, 120) }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</section>
