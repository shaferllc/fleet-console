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
        <p class="mt-1.5 text-xs text-zinc-500">Lowercase letters, numbers, and hyphens only (used in URLs).</p>
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
        @error('name')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-zinc-300">Description</label>
        <textarea name="description" id="description" rows="3"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="Optional">{{ old('description', $target?->description) }}</textarea>
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
        @error('site_url')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="operator_path_prefix" class="block text-sm font-medium text-zinc-300">Operator path prefix</label>
        <input type="text" name="operator_path_prefix" id="operator_path_prefix"
            value="{{ old('operator_path_prefix', $target?->operator_path_prefix ?? '/api/operator') }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="/api/operator">
        @error('operator_path_prefix')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="operator_token" class="block text-sm font-medium text-zinc-300">Per-target operator token</label>
        <input type="password" name="operator_token" id="operator_token" autocomplete="new-password"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600"
            placeholder="{{ $isEdit ? 'Leave blank to keep current token' : 'Optional; uses FLEET_OPERATOR_TOKEN if empty' }}">
        @error('operator_token')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
        @if ($isEdit)
            <label class="mt-3 flex items-center gap-2 text-sm text-zinc-400">
                <input type="hidden" name="clear_operator_token" value="0">
                <input type="checkbox" name="clear_operator_token" value="1" class="rounded border-zinc-600 bg-zinc-900 text-cyan-500 focus:ring-cyan-500/40" @checked(old('clear_operator_token'))>
                Clear stored per-target token
            </label>
        @endif
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-zinc-300">Sort order</label>
        <input type="number" name="sort_order" id="sort_order" min="0"
            value="{{ old('sort_order', $target?->sort_order ?? 0) }}"
            class="fc-input mt-2 block w-full rounded-xl border border-zinc-700/80 bg-zinc-950/80 px-4 py-3 text-sm text-white placeholder:text-zinc-600">
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
        @error('is_enabled')
            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
