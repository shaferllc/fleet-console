@php
    $variant = $variant ?? 'header';
    $wrap = match ($variant) {
        'login' => 'mx-auto h-16 w-16 rounded-2xl p-[2px] shadow-xl shadow-cyan-500/15',
        default => 'h-10 w-10 rounded-xl p-[2px] shadow-lg shadow-cyan-500/10',
    };
    $imgRadius = $variant === 'login' ? 'rounded-[14px]' : 'rounded-[10px]';
    $dim = $variant === 'login' ? 64 : 40;
@endphp

<div class="fc-logo-ring relative flex shrink-0 items-center justify-center {{ $wrap }}">
    <img
        src="{{ asset('images/fleet-logo.png') }}"
        alt="Fleet"
        width="{{ $dim }}"
        height="{{ $dim }}"
        class="h-full w-full {{ $imgRadius }} object-contain bg-zinc-950"
        decoding="async"
    >
</div>
