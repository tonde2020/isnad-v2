@auth
<div
    x-data="{
        time: '00:00:00',
        period: '--',
        date: 'جاري التحميل...',
        interval: null,
        update() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            this.period = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            this.time = `${String(hours).padStart(2, '0')}:${minutes}:${seconds}`;
            this.date = now.toLocaleDateString('ar-EG', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },
        init() {
            this.update();
            this.interval = setInterval(() => this.update(), 1000);
        },
        destroy() {
            clearInterval(this.interval);
        },
    }"
    class="mx-4 mb-3 rounded-lg border border-[#e5e1d8] bg-white/70 p-3 shadow-sm backdrop-blur-sm md:hidden dark:border-white/10 dark:bg-white/10"
>
    <div class="m-1 flex items-center justify-between gap-3">
        <div>
            <div class="flex items-baseline">
                <span
                    x-text="time"
                    class="text-base font-bold tracking-wider text-[#4a3f35] dark:text-[#f8ead0]"
                ></span>

                <span
                    x-text="period"
                    class="ms-2 text-[10px] font-semibold text-gray-500 dark:text-gray-300"
                ></span>
            </div>

            <p
                x-text="date"
                class="mt-0.5 max-w-32 truncate text-[10px] font-medium text-gray-500 dark:text-gray-300"
            ></p>
        </div>

        <div class="flex items-center gap-1.5 rounded-full bg-[#fcfaf2] px-2.5 py-1 dark:bg-white/10">
            <div class="iqra-weather-icon text-lg">
                🌤️
            </div>

            <div class="text-right">
                <p class="text-[10px] leading-none text-gray-500 dark:text-gray-300">الخرطوم</p>
                <p class="text-xs font-bold leading-tight text-[#4a3f35] dark:text-[#f8ead0]">32°C</p>
            </div>
        </div>
    </div>
</div>
@endauth
