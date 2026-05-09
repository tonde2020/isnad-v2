<x-layouts.app title="دليل مكونات إقرأ">
    <div class="mb-10">
        <p class="text-sm font-bold text-primary">Starter Kit</p>
        <h1 class="mt-2 text-3xl font-extrabold text-ink">دليل مكونات إقرأ</h1>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-gray-600">
            صفحة مرجعية سريعة لمراجعة الأزرار، الحقول، البطاقات، والنوافذ قبل بناء منطق العمل.
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-card>
            <h2 class="mb-4 text-lg font-bold text-ink">الأزرار</h2>

            <div class="flex flex-wrap gap-3">
                <x-button>زر أساسي</x-button>
                <x-button variant="secondary">زر ثانوي</x-button>
                <x-button variant="ghost">زر هادئ</x-button>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-bold text-ink">حقول الإدخال</h2>

            <div class="space-y-4">
                <x-input label="اسم الطالب" placeholder="مثال: محمد أحمد" />
                <x-input label="البريد الإلكتروني" type="email" placeholder="student@example.com" hint="هذا النص يستخدم كملاحظة مساعدة." />
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-bold text-ink">بطاقة إحصائية</h2>

            <div class="flex items-center justify-between rounded-2xl border border-border-soft bg-white p-5">
                <div>
                    <p class="text-sm font-semibold text-gray-500">إجمالي الطلاب</p>
                    <p class="mt-1 text-3xl font-extrabold text-ink">--</p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent text-xl font-bold text-primary">
                    +
                </div>
            </div>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-bold text-ink">نافذة Modal</h2>

            <x-modal name="guide-modal" title="نافذة تجريبية">
                <x-slot:trigger>
                    <x-button x-on:click="$dispatch('open-modal', 'guide-modal')">
                        فتح النافذة
                    </x-button>
                </x-slot:trigger>

                <p class="text-sm leading-7 text-gray-600">
                    هذا مكون Modal جاهز مبني بـ Alpine.js ويمكن استخدامه في أي صفحة عامة.
                </p>
            </x-modal>
        </x-card>
    </div>
</x-layouts.app>
