@props([
    'name',
    'title' => null,
])

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="open = false"
    x-on:keydown.escape.window="open = false"
>
    {{ $trigger ?? '' }}

    <div
        x-cloak
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-start justify-center bg-[#121110]/40 px-4 pt-24 backdrop-blur-sm"
    >
        <div
            x-on:click.away="open = false"
            x-transition.scale.origin.top
            class="w-full max-w-lg overflow-hidden rounded-2xl border border-border-soft bg-white shadow-2xl"
        >
            @if ($title)
                <div class="border-b border-border-soft px-6 py-4">
                    <h2 class="text-lg font-bold text-ink">{{ $title }}</h2>
                </div>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
