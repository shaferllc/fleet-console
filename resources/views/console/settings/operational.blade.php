@extends('layouts.console')

@section('title', 'Console settings')

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
        href="{{ route('console.settings.alerts') }}"
        class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-cyan-500/35 hover:bg-zinc-800/70 hover:text-cyan-100"
    >
        Alerts
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
        <h1 class="fc-heading mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Console settings</h1>
        <p class="mt-3 text-base leading-relaxed text-zinc-400">
            Polling, TLS verification, read-only API token, trusted IPs, health check token, and background scheduler behavior. Optional bcrypt hash below replaces <code class="font-mono text-xs text-zinc-300">FLEET_CONSOLE_PASSWORD_HASH</code> in <code class="font-mono text-xs text-zinc-300">.env</code> for shared console login when Fleet IdP password grant is not configured.
        </p>
    </header>

    @if (session('status'))
        <div class="fc-glass mt-8 rounded-xl border border-emerald-500/25 p-4 text-sm text-emerald-100/95">
            {{ session('status') }}
        </div>
    @endif

    <form method="post" action="{{ route('console.settings.operational.update') }}" class="fc-glass mt-10 max-w-3xl space-y-6 rounded-2xl p-8">
        @csrf
        @method('PUT')

        <div>
            <label for="password_hash" class="block text-sm font-medium text-zinc-300">{{ __('Console login password hash (bcrypt, optional)') }}</label>
            <textarea name="password_hash" id="password_hash" rows="2"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 font-mono text-xs text-zinc-200 placeholder:text-zinc-600"
                placeholder="$2y$12$…">{{ old('password_hash', '') }}</textarea>
            <p class="mt-1.5 text-xs text-zinc-500">{{ __('Stored in the database. When empty, `FLEET_CONSOLE_PASSWORD_HASH` in `.env` is used if set.') }}</p>
            @error('password_hash')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="hidden" name="http_verify" value="0">
            <label class="flex items-center gap-2 text-sm text-zinc-300">
                <input type="checkbox" name="http_verify" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40"
                    @checked(old('http_verify', $settings->http_verify))>
                {{ __('Verify TLS certificates when polling operator APIs and README fetches') }}
            </label>
        </div>

        <div>
            <label for="daily_rollup_sparkline_after_samples" class="block text-sm font-medium text-zinc-300">{{ __('Daily rollup sparkline threshold (sample count)') }}</label>
            <input type="number" name="daily_rollup_sparkline_after_samples" id="daily_rollup_sparkline_after_samples" required min="0" max="10000000"
                value="{{ old('daily_rollup_sparkline_after_samples', $settings->daily_rollup_sparkline_after_samples) }}"
                class="fc-input mt-2 block w-full max-w-xs rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white">
            @error('daily_rollup_sparkline_after_samples')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="api_token" class="block text-sm font-medium text-zinc-300">{{ __('Read-only API token') }}</label>
            <input type="password" name="api_token" id="api_token" autocomplete="new-password"
                value="{{ old('api_token', '') }}"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                placeholder="{{ __('Leave blank to keep current token') }}">
            <input type="hidden" name="clear_api_token" value="0">
            <label class="mt-3 flex items-center gap-2 text-sm text-zinc-400">
                <input type="checkbox" name="clear_api_token" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40" @checked(old('clear_api_token'))>
                {{ __('Clear API token (disables /api/fleet/* until set again)') }}
            </label>
            @error('api_token')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="trusted_ips" class="block text-sm font-medium text-zinc-300">{{ __('Trusted IPs (optional)') }}</label>
            <textarea name="trusted_ips" id="trusted_ips" rows="3"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 font-mono text-xs text-zinc-200 placeholder:text-zinc-600"
                placeholder="127.0.0.1,::1,10.0.0.0/8">{{ old('trusted_ips', $settings->trusted_ips) }}</textarea>
            <p class="mt-1.5 text-xs text-zinc-500">{{ __('Comma-separated IPs or CIDRs. Empty = no IP restriction on console and protected fleet API routes.') }}</p>
            @error('trusted_ips')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="health_token" class="block text-sm font-medium text-zinc-300">{{ __('Health endpoint token (optional)') }}</label>
            <input type="password" name="health_token" id="health_token" autocomplete="new-password"
                value="{{ old('health_token', '') }}"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                placeholder="{{ __('Leave blank to keep current token') }}">
            <input type="hidden" name="clear_health_token" value="0">
            <label class="mt-3 flex items-center gap-2 text-sm text-zinc-400">
                <input type="checkbox" name="clear_health_token" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40" @checked(old('clear_health_token'))>
                {{ __('Clear health token (public health endpoint)') }}
            </label>
            @error('health_token')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="hidden" name="background_poll_enabled" value="0">
            <label class="flex items-center gap-2 text-sm text-zinc-300">
                <input type="checkbox" name="background_poll_enabled" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40"
                    @checked(old('background_poll_enabled', $settings->background_poll_enabled))>
                {{ __('Enable scheduled background polling (requires cron or `php artisan schedule:work`)') }}
            </label>
        </div>

        <div>
            <label for="poll_interval_minutes" class="block text-sm font-medium text-zinc-300">{{ __('Poll interval (minutes, 1–120)') }}</label>
            <input type="number" name="poll_interval_minutes" id="poll_interval_minutes" required min="1" max="120"
                value="{{ old('poll_interval_minutes', $settings->poll_interval_minutes) }}"
                class="fc-input mt-2 block w-full max-w-xs rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white">
            @error('poll_interval_minutes')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="fc-btn-primary rounded-xl px-5 py-3 text-sm font-semibold text-white">
            {{ __('Save') }}
        </button>
    </form>
@endsection
