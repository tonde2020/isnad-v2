@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $classes = [
        'primary' => 'bg-primary text-primary-foreground shadow-sm hover:brightness-105 focus-visible:ring-primary/40',
        'secondary' => 'border border-border-soft bg-surface text-ink shadow-sm hover:bg-white focus-visible:ring-primary/30',
        'ghost' => 'text-ink hover:bg-white/60 focus-visible:ring-primary/30',
    ][$variant] ?? 'bg-primary text-primary-foreground shadow-sm hover:brightness-105 focus-visible:ring-primary/40';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "{$classes} inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-60",
    ]) }}
>
    {{ $slot }}
</button>
