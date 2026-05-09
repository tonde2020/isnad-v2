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
    class="hidden items-center gap-4 rounded-full border border-[#e5e1d8] bg-white/45 px-4 py-1.5 shadow-sm backdrop-blur-sm xl:flex dark:border-white/10 dark:bg-white/10"
>
    <div class="flex items-baseline">
        <span
            x-text="time"
            class="text-lg font-bold tracking-wider text-[#4a3f35] dark:text-[#f8ead0]"
        ></span>

        <span
            x-text="period"
            class="ms-2 text-[10px] font-semibold text-gray-500 dark:text-gray-300"
        ></span>
    </div>

    <div class="hidden border-s border-[#d9d1c3] ps-4 2xl:block dark:border-white/15">
        <p
            x-text="date"
            class="text-xs font-medium text-gray-600 dark:text-gray-300"
        ></p>
    </div>

    <div class="flex items-center gap-2 rounded-full bg-white/45 px-3 py-1 dark:bg-white/10">
        <div class="iqra-weather-icon text-xl">
            🌤️
        </div>

        <div class="text-right">
            <p class="text-[10px] leading-none text-gray-500 dark:text-gray-300">الخرطوم</p>
            <p class="text-sm font-bold leading-tight text-[#4a3f35] dark:text-[#f8ead0]">32°C</p>
        </div>
    </div>
</div>
