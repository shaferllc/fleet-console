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
        <p class="mt-2 text-sm text-zinc-400">Targets the operator summary endpoint under your path prefix.</p>
    </header>

    <div class="fc-glass mt-8 max-w-2xl rounded-2xl p-8">
        <form method="post" action="{{ route('console.targets.store') }}" class="space-y-8">
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
