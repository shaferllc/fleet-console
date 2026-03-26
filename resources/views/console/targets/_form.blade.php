@php
    /** @var \App\Models\FleetTarget|null $target */
    $isEdit = $target !== null;
@endphp

<div class="space-y-5">
    <div>
        <label for="key" class="block text-sm font-medium text-zinc-300">Key</label>
        <input type="text" name="key" id="key" required
            value="{{ old('key', $target?->key) }}"
            pattern="[a-z0-9-]+"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="beacon">
        <p class="mt-1.5 text-xs text-zinc-500">Lowercase letters, numbers, hyphens only. <span class="text-zinc-400">Where:</span> only this form / Fleet DB — not on the target app.</p>
        @error('key')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-zinc-300">Display name</label>
        <input type="text" name="name" id="name" required
            value="{{ old('name', $target?->name) }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="Beacon">
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> only this form — label on the dashboard card.</p>
        @error('name')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-zinc-300">Description</label>
        <textarea name="description" id="description" rows="3"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="Optional">{{ old('description', $target?->description) }}</textarea>
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> only this form — optional subtitle on the card.</p>
        @error('description')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="base_url" class="block text-sm font-medium text-zinc-300">Operator base URL</label>
        <input type="url" name="base_url" id="base_url" required
            value="{{ old('base_url', $target?->base_url) }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="https://beacon.example.com">
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> only this form — public origin Fleet uses to call <code class="font-mono text-cyan-200/80">…/summary</code> (scheme + host, no path).</p>
        @error('base_url')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="site_url" class="block text-sm font-medium text-zinc-300">Public site URL</label>
        <input type="url" name="site_url" id="site_url"
            value="{{ old('site_url', $target?->site_url) }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="Defaults to operator base if empty">
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> only this form — optional “Open site” link; leave empty to use operator base URL.</p>
        @error('site_url')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="staging_site_url" class="block text-sm font-medium text-zinc-300">Staging site URL</label>
        <input type="url" name="staging_site_url" id="staging_site_url"
            value="{{ old('staging_site_url', $target?->staging_site_url) }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="https://staging.example.com">
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> optional link to staging / preview; shown on the card and README next to production.</p>
        @error('staging_site_url')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="operator_path_prefix" class="block text-sm font-medium text-zinc-300">Operator path prefix</label>
        <input type="text" name="operator_path_prefix" id="operator_path_prefix"
            value="{{ old('operator_path_prefix', $target?->operator_path_prefix ?? '/api/operator') }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="/api/operator">
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> must match the target app’s operator routes (often <code class="font-mono text-cyan-200/80">/api/operator</code>; some apps use <code class="font-mono text-cyan-200/80">/api/v1/operator</code>).</p>
        @error('operator_path_prefix')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="fleet-operator-token-toolkit" data-fleet-operator-token-toolkit>
        <label for="operator_token" class="block text-sm font-medium text-zinc-300">Operator token</label>
        <input type="password" name="operator_token" id="operator_token" autocomplete="new-password"
            @if (! $isEdit) required @endif
            value="{{ old('operator_token', '') }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="{{ $isEdit ? 'Leave blank to keep current token' : 'Generate or paste (min 8 characters), then Save' }}">
        <div class="mt-2 flex flex-wrap gap-2">
            <button type="button" data-fleet-generate-operator-token
                class="rounded-lg border border-cyan-500/35 bg-cyan-950/40 px-3 py-2 text-xs font-medium text-cyan-100 transition hover:border-cyan-400/50 hover:bg-cyan-900/35">
                Generate token
            </button>
            <button type="button" data-fleet-copy-operator-token-only
                class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70">
                Copy token
            </button>
            <button type="button" data-fleet-copy-operator-target-env
                class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70">
                Copy for target .env
            </button>
        </div>
        <p class="mt-2 min-h-[1.25rem] text-xs text-emerald-400/90" data-fleet-operator-token-status role="status" aria-live="polite"></p>
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Flow:</span> Generate → <strong class="font-medium text-zinc-400">Copy for target .env</strong> → paste into that app’s environment as <code class="font-mono text-zinc-400">FLEET_OPERATOR_TOKEN=…</code> (or the name that app uses) → <strong class="font-medium text-zinc-400">Save</strong> here so Fleet stores the same secret. Each service has its own token.</p>
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> stored encrypted in Fleet for this service only; must match the operator bearer secret on that target app.</p>
        @error('operator_token')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
        @if ($isEdit)
            <label class="mt-3 flex items-center gap-2 text-sm text-zinc-400">
                <input type="hidden" name="clear_operator_token" value="0">
                <input type="checkbox" name="clear_operator_token" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40" @checked(old('clear_operator_token'))>
                Clear stored operator token
            </label>
        @endif
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-zinc-300">Sort order</label>
        <input type="number" name="sort_order" id="sort_order" min="0"
            value="{{ old('sort_order', $target?->sort_order ?? 0) }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600">
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> only this form — order in the Services list (lower first).</p>
        @error('sort_order')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <input type="hidden" name="is_enabled" value="0">
        <label class="flex items-center gap-2 text-sm text-zinc-300">
            <input type="checkbox" name="is_enabled" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40"
                @checked(old('is_enabled', $target === null ? true : $target->is_enabled))>
            Enabled (included in dashboard and polls)
        </label>
        <p class="mt-1.5 text-xs text-zinc-500"><span class="text-zinc-400">Where:</span> only this form — off pauses polling and hides the card.</p>
        @error('is_enabled')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="border-t border-zinc-800/80 pt-6">
        <h3 class="text-xs font-semibold uppercase tracking-[0.18em] text-cyan-500/90">Alerts (optional)</h3>
        <p class="mt-2 text-xs leading-relaxed text-zinc-500">Global destinations live under <a href="{{ route('console.settings.alerts') }}" class="font-medium text-cyan-400 underline decoration-cyan-500/35 underline-offset-2 hover:text-cyan-300">Alert settings</a>. Here you can mute this service, override SLO thresholds, or add webhooks for this key only.</p>

        <div class="mt-4">
            <input type="hidden" name="mute_alerts" value="0">
            <label class="flex items-center gap-2 text-sm text-zinc-300">
                <input type="checkbox" name="mute_alerts" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40"
                    @checked(old('mute_alerts', $target?->mute_alerts))>
                Mute all alerts for this service
            </label>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="alert_slo_min_ok_percent" class="block text-sm font-medium text-zinc-300">Override SLO min 24h OK %</label>
                <input type="number" name="alert_slo_min_ok_percent" id="alert_slo_min_ok_percent" step="0.001" min="0" max="100"
                    value="{{ old('alert_slo_min_ok_percent', $target?->alert_slo_min_ok_percent) }}"
                    class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                    placeholder="Use global default if empty">
                @error('alert_slo_min_ok_percent')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="alert_slo_dedupe_hours" class="block text-sm font-medium text-zinc-300">Override SLO dedupe (hours)</label>
                <input type="number" name="alert_slo_dedupe_hours" id="alert_slo_dedupe_hours" min="1" max="8760"
                    value="{{ old('alert_slo_dedupe_hours', $target?->alert_slo_dedupe_hours) }}"
                    class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
                    placeholder="Use global default if empty">
                @error('alert_slo_dedupe_hours')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <label for="alert_webhook_urls_json" class="block text-sm font-medium text-zinc-300">Extra webhook URLs (JSON array)</label>
            <textarea name="alert_webhook_urls_json" id="alert_webhook_urls_json" rows="3"
                class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 font-mono text-xs text-zinc-200 placeholder:text-zinc-600"
                placeholder='["https://example.com/hook"]'>{{ old('alert_webhook_urls_json', isset($target) && $target !== null && is_array($target->alert_webhook_urls) && $target->alert_webhook_urls !== [] ? json_encode($target->alert_webhook_urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
            @error('alert_webhook_urls_json')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
