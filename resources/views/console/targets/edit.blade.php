@extends('layouts.console')

@section('title', 'Edit service')

@section('topbar_actions')
    <a href="{{ route('console.targets.index') }}" class="rounded-lg border border-zinc-600/50 bg-zinc-900/50 px-3 py-2 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:bg-zinc-800/70 hover:text-white">
        Back
    </a>
@endsection

@section('content')
    <header class="max-w-3xl">
        <h1 class="fc-heading text-2xl font-bold tracking-tight text-white sm:text-3xl">Edit {{ $target->name }}</h1>
        <p class="mt-2 text-sm text-zinc-400">Key <span class="font-mono text-cyan-200/80">{{ $target->key }}</span></p>
    </header>

    <div class="fc-glass mt-8 max-w-3xl rounded-2xl border border-cyan-500/15 p-6 sm:p-8">
        <h2 class="fc-heading text-lg font-semibold tracking-tight text-white">Reference</h2>
        <p class="mt-1 text-xs text-zinc-500">Same checklist as on Add service — what belongs in Fleet <code class="font-mono text-zinc-400">.env</code> vs this form vs the target app.</p>
        <div class="mt-4">
            @include('console.targets._where_what_reference')
        </div>
    </div>

    <div class="fc-glass mt-8 max-w-2xl rounded-2xl p-8">
        <h2 class="fc-heading text-lg font-semibold tracking-tight text-white">Service details</h2>
        <p class="mt-1 text-xs text-zinc-500">Hints under each field say where the value lives. Per-target token: leave blank to keep the current stored token.</p>
        <form method="post" action="{{ route('console.targets.update', $target) }}" class="mt-8 space-y-8">
            @csrf
            @method('PUT')
            @include('console.targets._form', ['target' => $target])
            <div class="flex gap-3">
                <button type="submit" class="fc-btn-primary rounded-xl px-5 py-2.5 text-sm font-semibold text-white">
                    Update
                </button>
                <a href="{{ route('console.targets.index') }}" class="rounded-xl border border-zinc-600/50 px-5 py-2.5 text-sm font-medium text-zinc-300 hover:bg-zinc-800/60">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
