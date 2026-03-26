@extends('layouts.console')

@section('title', 'Alerts')

@section('topbar_actions')
    <a
        href="{{ route('console.dashboard') }}"
        class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70 hover:text-white"
    >
        Dashboard
    </a>
    <a
        href="{{ route('console.targets.index') }}"
        class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-cyan-500/35 hover:bg-zinc-800/70 hover:text-cyan-100"
    >
        Services
    </a>
    <a
        href="{{ route('console.settings.operational') }}"
        class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-cyan-500/35 hover:bg-zinc-800/70 hover:text-cyan-100"
    >
        Console
    </a>
    <form method="post" action="{{ route('console.logout') }}" class="inline">
        @csrf
        <button type="submit" class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70 hover:text-white">
            Sign out
        </button>
    </form>
@endsection

@section('content')
    <header class="max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-500/90">Configuration</p>
        <h1 class="fc-heading mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Alert settings</h1>
        <p class="mt-3 text-base leading-relaxed text-zinc-400">
            Down / recovery / SLO / metric alerts use these destinations. Per-service overrides (mute, extra webhooks, SLO) live on each service under <a href="{{ route('console.targets.index') }}" class="font-medium text-cyan-400 underline decoration-cyan-500/35 underline-offset-2 hover:text-cyan-300">Services</a>.
        </p>
    </header>

    @if (session('status'))
        <div class="fc-glass mt-8 rounded-xl border border-emerald-500/25 p-4 text-sm text-emerald-100/95">
            {{ session('status') }}
        </div>
    @endif

    <form method="post" action="{{ route('console.settings.alerts.update') }}" class="fc-glass mt-10 max-w-3xl space-y-6 rounded-2xl p-8">
        @csrf
        @method('PUT')

        <div>
            <label for="alert_email" class="block text-sm font-medium text-zinc-300">{{ __('Alert email') }}</label>
            <input type="email" name="alert_email" id="alert_email"
                value="{{ old('alert_email', $settings->alert_email) }}"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                placeholder="ops@example.com"
                autocomplete="email">
            @error('alert_email')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="alert_slack_webhook" class="block text-sm font-medium text-zinc-300">{{ __('Slack incoming webhook URL') }}</label>
            <input type="url" name="alert_slack_webhook" id="alert_slack_webhook"
                value="{{ old('alert_slack_webhook', $settings->alert_slack_webhook) }}"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                placeholder="https://hooks.slack.com/services/…">
            @error('alert_slack_webhook')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="alert_webhook_urls_json" class="block text-sm font-medium text-zinc-300">{{ __('Custom webhook URLs (JSON array)') }}</label>
            <textarea name="alert_webhook_urls_json" id="alert_webhook_urls_json" rows="4"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 font-mono text-xs text-zinc-200 placeholder:text-zinc-600"
                placeholder='["https://example.com/hooks/fleet"]'>{{ old('alert_webhook_urls_json', $webhookUrlsJson) }}</textarea>
            @error('alert_webhook_urls_json')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="hidden" name="alert_on_recovery" value="0">
            <label class="flex items-center gap-2 text-sm text-zinc-300">
                <input type="checkbox" name="alert_on_recovery" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40"
                    @checked(old('alert_on_recovery', $settings->alert_on_recovery))>
                {{ __('Send alerts when a target recovers (OK after down)') }}
            </label>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="alert_slo_min_ok_percent" class="block text-sm font-medium text-zinc-300">{{ __('SLO: min 24h OK % (optional)') }}</label>
                <input type="number" name="alert_slo_min_ok_percent" id="alert_slo_min_ok_percent" step="0.001" min="0" max="100"
                    value="{{ old('alert_slo_min_ok_percent', $settings->alert_slo_min_ok_percent) }}"
                    class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                    placeholder="e.g. 99">
                @error('alert_slo_min_ok_percent')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="alert_slo_dedupe_hours" class="block text-sm font-medium text-zinc-300">{{ __('SLO alert dedupe (hours)') }}</label>
                <input type="number" name="alert_slo_dedupe_hours" id="alert_slo_dedupe_hours" required min="1" max="8760"
                    value="{{ old('alert_slo_dedupe_hours', $settings->alert_slo_dedupe_hours) }}"
                    class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600">
                @error('alert_slo_dedupe_hours')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="alert_metric_rules_json" class="block text-sm font-medium text-zinc-300">{{ __('Metric rules (JSON)') }}</label>
            <p class="mt-1 text-xs leading-relaxed text-zinc-500">Keys are target keys or <code class="font-mono text-zinc-400">*</code> for all. Each value is a list of <code class="font-mono text-zinc-400">path</code> / <code class="font-mono text-zinc-400">min</code> / <code class="font-mono text-zinc-400">max</code> rules on operator summary JSON.</p>
            <textarea name="alert_metric_rules_json" id="alert_metric_rules_json" rows="12"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 font-mono text-xs text-zinc-200 placeholder:text-zinc-600"
                placeholder='{"*":[{"path":"users","min":1}]}'>{{ old('alert_metric_rules_json', $metricRulesJson) }}</textarea>
            @error('alert_metric_rules_json')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="fc-btn-primary rounded-xl px-5 py-3 text-sm font-semibold text-white">
            {{ __('Save') }}
        </button>
    </form>
@endsection
