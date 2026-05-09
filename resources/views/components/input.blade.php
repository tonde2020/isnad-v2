@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'hint' => null,
])

<label class="block">
    @if ($label)
        <span class="mb-2 block text-sm font-bold text-ink">{{ $label }}</span>
    @endif

    <input
        type="{{ $type }}"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-xl border border-border-soft bg-surface px-4 py-2.5 text-sm text-ink shadow-sm transition placeholder:text-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20',
        ]) }}
    >

    @if ($hint)
        <span class="mt-1 block text-xs text-gray-500">{{ $hint }}</span>
    @endif
</label>
