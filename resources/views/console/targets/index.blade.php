@extends('layouts.console')

@section('title', 'Services')

@section('topbar_actions')
    <a
        href="{{ route('console.dashboard') }}"
        class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70 hover:text-white"
    >
        Dashboard
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
        <h1 class="fc-heading mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">Services</h1>
        <p class="mt-3 text-base leading-relaxed text-zinc-400">
            When at least one service is enabled here, this list replaces file and <code class="font-mono text-xs text-zinc-300">FLEET_CONSOLE_TARGETS</code> defaults. Leave the database empty to keep using env/config.
        </p>
        <p class="mt-3 text-sm leading-relaxed text-zinc-500">
            Open <a href="{{ route('console.targets.create') }}" class="font-medium text-cyan-400 underline decoration-cyan-500/35 underline-offset-2 hover:text-cyan-300">Add service</a>
            or any <strong class="font-medium text-zinc-400">Edit</strong> link for a printable-style table: what goes in the target app <code class="font-mono text-xs text-zinc-400">.env</code>, what goes in Fleet’s <code class="font-mono text-xs text-zinc-400">.env</code>, and what goes in each form field.
        </p>
    </header>

    @if (session('status'))
        <div class="fc-glass mt-8 rounded-xl border border-emerald-500/25 p-4 text-sm text-emerald-100/95">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fc-glass mt-8 rounded-xl border border-red-500/25 p-4 text-sm text-red-200/95">
            {{ session('error') }}
        </div>
    @endif

    <div class="mt-10 flex flex-wrap items-center gap-3">
        <a href="{{ route('console.targets.create') }}" class="fc-btn-primary inline-flex rounded-xl px-4 py-2.5 text-sm font-semibold text-white">
            Add service
        </a>
        @if ($catalogCount > 0)
            <form method="post" action="{{ route('console.targets.import') }}" class="inline" onsubmit="return confirm('Import missing services from the built-in catalog? Existing keys are not changed.');">
                @csrf
                <button type="submit" class="rounded-xl border border-zinc-600/50 bg-zinc-900/50 px-4 py-2.5 text-sm font-medium text-zinc-200 transition hover:border-cyan-500/35 hover:bg-zinc-800/70 hover:text-cyan-100">
                    Import catalog ({{ $catalogCount }})
                </button>
            </form>
        @endif
    </div>

    <div class="fc-glass mt-8 overflow-hidden rounded-2xl">
        @if ($targets->isEmpty())
            <p class="p-8 text-sm text-zinc-400">No services in the database yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-800 text-left text-sm">
                    <thead class="bg-zinc-950/50 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                        <tr>
                            <th class="px-4 py-3">Key</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Base URL</th>
                            <th class="px-4 py-3">Enabled</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/80 text-zinc-200">
                        @foreach ($targets as $t)
                            <tr class="hover:bg-zinc-900/40">
                                <td class="px-4 py-3 font-mono text-xs text-cyan-200/90">{{ $t->key }}</td>
                                <td class="px-4 py-3">{{ $t->name }}</td>
                                <td class="max-w-xs truncate px-4 py-3 font-mono text-xs text-zinc-400">{{ $t->base_url }}</td>
                                <td class="px-4 py-3">
                                    @if ($t->is_enabled)
                                        <span class="text-emerald-400/90">Yes</span>
                                    @else
                                        <span class="text-zinc-500">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('console.targets.edit', $t) }}" class="text-cyan-400 hover:text-cyan-300">Edit</a>
                                    <form method="post" action="{{ route('console.targets.destroy', $t) }}" class="ml-3 inline" onsubmit="return confirm('Remove this service from the database?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400/90 hover:text-red-300">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
