@extends('layouts.console')

@section('title', 'Add service')

@section('topbar_actions')
    <a href="{{ route('console.targets.index') }}" class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70 hover:text-white">
        Back
    </a>
@endsection

@section('content')
    <header class="max-w-3xl">
        <h1 class="fc-heading text-2xl font-bold tracking-tight text-white sm:text-3xl">Add service</h1>
        <p class="mt-2 text-sm text-zinc-400">
            Register one product (e.g. Waypost, Beacon) so Fleet can poll its operator API and open its README.
        </p>
    </header>

    <div class="fc-glass mt-8 max-w-3xl rounded-2xl border border-cyan-500/15 p-6 sm:p-8">
        <h2 class="fc-heading text-lg font-semibold tracking-tight text-white">Before you save — full setup</h2>
        <p class="mt-2 text-sm leading-relaxed text-zinc-400">
            Fleet calls your app over HTTP; the bearer token must match on both sides.
        </p>

        <div class="mt-6">
            @include('console.targets._where_what_reference')
        </div>

        <div class="mt-8 space-y-6 text-sm leading-relaxed text-zinc-300">
            <section>
                <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-500/90">1. On the target app</h3>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-zinc-400">
                    <li>
                        The app must expose the <strong class="font-medium text-zinc-300">operator JSON API</strong> Fleet uses for health summaries.
                        Fleet requests your
                        <strong class="font-medium text-zinc-300">operator base URL</strong>
                        + <strong class="font-medium text-zinc-300">operator path prefix</strong>
                        + <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-cyan-200/90">/summary</code>
                        (and the README view uses the same prefix +
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-cyan-200/90">/readme</code>).
                    </li>
                    <li>
                        Default prefix is <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-cyan-200/90">/api/operator</code>
                        (full summary URL example:
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-cyan-200/90">https://waypost.example.com/api/operator/summary</code>).
                        Some stacks use another prefix (e.g. Dply-style
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-cyan-200/90">/api/v1/operator</code>) — set that exact value in the form.
                    </li>
                    <li>
                        On that app, configure its operator bearer token (often
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-zinc-300">FLEET_OPERATOR_TOKEN</code>
                        or the name in that product’s docs). Use a long random secret.
                    </li>
                </ul>
            </section>

            <section>
                <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-500/90">2. On Fleet Console (this app)</h3>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-zinc-400">
                    <li>
                        Sign in to this console with <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-zinc-300">FLEET_CONSOLE_PASSWORD</code>
                        in <code class="font-mono text-xs text-zinc-300">.env</code> (separate from operator tokens).
                    </li>
                    <li>
                        For each service, set the <strong class="font-medium text-zinc-300">Operator token</strong> in the form below to the <strong class="font-medium text-zinc-300">same</strong> secret as that target app’s operator token (often <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-zinc-300">FLEET_OPERATOR_TOKEN</code> there). Fleet stores one token per service — there is no shared operator token in Fleet’s <code class="font-mono text-xs text-zinc-300">.env</code>.
                    </li>
                    <li>
                        <strong class="font-medium text-zinc-300">Operator base URL</strong> is the origin only (no path): e.g.
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-cyan-200/90">https://waypost.example.com</code>.
                        Must be reachable from this server (firewall, DNS, TLS).
                    </li>
                    <li>
                        <strong class="font-medium text-zinc-300">Key</strong> is a short slug (<code class="font-mono text-xs text-cyan-200/90">waypost</code>,
                        <code class="font-mono text-xs text-cyan-200/90">beacon</code>, …). It appears in console URLs and stored polls; use the same key you would plug into
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-zinc-300">FLEET_CONSOLE_TARGET_URL_TEMPLATE</code>
                        if you used the file catalog (<code class="font-mono text-xs text-cyan-200/90">{key}</code>).
                    </li>
                    <li>
                        <strong class="font-medium text-zinc-300">Public site URL</strong> is optional: the “Open site” link in the dashboard. If empty, Fleet uses the operator base URL.
                    </li>
                    <li>
                        If HTTPS to local or internal hosts fails from PHP with certificate errors, set
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-zinc-300">FLEET_CONSOLE_HTTP_VERIFY=false</code>
                        in <code class="font-mono text-xs text-zinc-300">.env</code> (prefer fixing the trust store / CA when you can).
                    </li>
                </ul>
            </section>

            <section>
                <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-500/90">3. After you save</h3>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-zinc-400">
                    <li>Open the <strong class="font-medium text-zinc-300">Dashboard</strong> and use <strong class="font-medium text-zinc-300">Refresh all</strong> (or refresh one card). Failed polls show as errors on the card with HTTP or connection details — the console itself should still load.</li>
                    <li>
                        The read-only HTTP API (<code class="font-mono text-xs text-zinc-300">/api/fleet/…</code>) is separate: enable it with
                        <code class="rounded bg-zinc-950/90 px-1.5 py-0.5 font-mono text-xs text-zinc-300">FLEET_CONSOLE_API_TOKEN</code> if you need it.
                    </li>
                </ul>
            </section>
        </div>
    </div>

    <div class="fc-glass mt-8 max-w-2xl rounded-2xl p-8">
        <h2 class="fc-heading text-lg font-semibold tracking-tight text-white">Service details</h2>
        <p class="mt-1 text-xs text-zinc-500">Each field has a short “where” hint below. The operator token is required per service and must match that app’s <code class="font-mono text-zinc-400">FLEET_OPERATOR_TOKEN</code> (or equivalent).</p>
        <form method="post" action="{{ route('console.targets.store') }}" class="mt-8 space-y-8">
            @csrf
            @include('console.targets._form', ['target' => null])
            <div class="flex gap-3">
                <button type="submit" class="fc-btn-primary rounded-xl px-5 py-2.5 text-sm font-semibold text-white">
                    Save
                </button>
                <a href="{{ route('console.targets.index') }}" class="rounded-xl border border-zinc-600/50 px-5 py-2.5 text-sm font-medium text-zinc-300 hover:bg-zinc-800/60">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
