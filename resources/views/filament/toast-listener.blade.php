<div
    x-data="{
        messages: [],
        remove(id) {
            this.messages = this.messages.filter((message) => message.id !== id);
        },
        pushToast(event) {
            const id = Date.now() + Math.random();
            const detail = event.detail ?? {};

            this.messages.push({
                id,
                type: detail.type ?? 'info',
                text: detail.text ?? detail.message ?? 'تم تنفيذ العملية بنجاح.',
            });

            setTimeout(() => this.remove(id), 4000);
        },
    }"
    x-on:toast.window="pushToast($event)"
    class="fixed bottom-6 right-6 z-[100] flex w-80 max-w-[calc(100vw-3rem)] flex-col gap-3"
    aria-live="polite"
>
    <template x-for="message in messages" :key="message.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="scale-90 opacity-0"
            class="relative flex items-center gap-3 overflow-hidden rounded-2xl border border-[#e5e1d8] bg-white p-4 shadow-lg dark:border-white/10 dark:bg-[#1f1c19]"
            role="status"
        >
            <div
                class="absolute bottom-0 left-0 top-0 w-1.5"
                x-bind:class="{
                    'bg-green-500': message.type === 'success',
                    'bg-blue-500': message.type === 'info',
                    'bg-red-500': message.type === 'error',
                    'bg-amber-500': message.type === 'warning',
                }"
            ></div>

            <div class="text-xl">
                <template x-if="message.type === 'success'">
                    <span>✓</span>
                </template>

                <template x-if="message.type === 'info'">
                    <span>i</span>
                </template>

                <template x-if="message.type === 'error'">
                    <span>!</span>
                </template>

                <template x-if="message.type === 'warning'">
                    <span>!</span>
                </template>
            </div>

            <div class="flex-1">
                <p
                    x-text="message.text"
                    class="text-sm font-bold text-[#4a3f35] dark:text-[#f8ead0]"
                ></p>
            </div>

            <button
                type="button"
                x-on:click="remove(message.id)"
                class="text-gray-300 transition hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-300"
                aria-label="إغلاق التنبيه"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
    </template>
</div>
