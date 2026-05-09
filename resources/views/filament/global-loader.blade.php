<div
    wire:loading.delay.flex
    class="pointer-events-none fixed inset-0 z-[90] hidden items-start justify-center bg-[#121110]/10 px-4 pt-24 backdrop-blur-[1px] dark:bg-black/20"
    aria-live="polite"
    aria-label="جاري المعالجة"
>
    <div class="flex items-center gap-3 rounded-2xl border border-[#e5e1d8] bg-white px-5 py-3 shadow-lg dark:border-white/10 dark:bg-[#1f1c19]">
        <svg
            class="h-5 w-5 animate-spin text-[#d4af37]"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>

        <span class="text-sm font-bold text-[#4a3f35] dark:text-[#f8ead0]">
            جاري المعالجة...
        </span>
    </div>
</div>
