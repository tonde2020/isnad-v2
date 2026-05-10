@php
    /** @var \App\Models\User|null $user */
    /** @var \App\Models\Patient|null $patient */
@endphp

<x-filament-widgets::widget>
    <div class="space-y-4">
        <div
            class="rounded-2xl border border-[#E8E0D4] bg-[#F5F1E9] px-4 py-4 text-sm leading-relaxed text-[#5C4D3C] shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-[#f8ead0]"
        >
            <p class="font-semibold">
                أنت تدير ملفك. حدّث بياناتك من «بياناتي» أو من تبويب «ملفي الطبي». الطبيب لا يطلع على ملفك إلا إذا أنشأت من «رابط للطبيب» رابطاً مؤقتاً وشاركته معه بنفسك.
            </p>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-xs font-bold text-[#8D7456] dark:text-[#d4af37]">احفظ لوحتي:</span>
                <input
                    type="text"
                    readonly
                    value="{{ $dashboardUrl }}"
                    class="w-full rounded-lg border border-[#E8E0D4] bg-white px-3 py-2 text-left font-mono text-xs text-[#4a3f35] shadow-inner dark:border-white/10 dark:bg-gray-950 dark:text-[#f8ead0] sm:max-w-xl"
                    onclick="this.select()"
                />
            </div>
        </div>

        @if ($patient === null)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-gray-600 dark:border-white/10 dark:bg-gray-900">
                لم يُربط بملف بعد. تواصل مع الدعم أو أنشئ حساباً من صفحة التسجيل.
            </div>
        @else
            <div
                class="overflow-hidden rounded-2xl border border-[#E8E0D4] bg-white shadow-md dark:border-white/10 dark:bg-gray-900"
            >
                <div class="border-b border-[#f0ebe3] bg-gradient-to-l from-[#fcfaf2] to-white px-6 py-6 dark:border-white/10 dark:from-gray-950 dark:to-gray-900">
                    <p class="text-2xl font-black text-[#4A3F35] dark:text-[#f8ead0]">
                        مرحباً، {{ $user->name }}
                    </p>
                    <p class="mt-2 text-sm font-semibold text-[#6D5F52] dark:text-gray-400">
                        الاسم في الملف الطبي: {{ $patient->full_name }}
                    </p>
                    @if (filled($patient->registry_number))
                        <p class="mt-2 inline-flex items-center gap-2 rounded-lg bg-[#fcfaf2] px-3 py-1.5 text-sm font-mono font-bold text-[#4A3F35] ring-1 ring-[#E8E0D4] dark:bg-gray-950 dark:text-[#f8ead0] dark:ring-white/10">
                            <span class="text-xs font-black text-[#8D7456] dark:text-[#d4af37]">رقم السجل:</span>
                            {{ $patient->registry_number }}
                        </p>
                    @endif
                </div>

                <div class="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div class="space-y-2 text-sm font-semibold text-[#5C4D3C] dark:text-gray-300">
                        <p><span class="text-[#8D7456]">الهاتف:</span> {{ $patient->phone }}</p>
                        <p>
                            <span class="text-[#8D7456]">تاريخ الميلاد:</span>
                            {{ $patient->birth_date?->format('d-m-Y') ?? '—' }}
                        </p>
                        <p><span class="text-[#8D7456]">فصيلة الدم:</span> {{ $patient->blood_type ?? '—' }}</p>
                    </div>

                    <div class="flex flex-col gap-3 justify-center">
                        @if ($profileViewUrl)
                            <a
                                href="{{ $profileViewUrl }}"
                                wire:navigate
                                class="inline-flex items-center justify-center rounded-xl bg-[#8D7456] px-4 py-3 text-center text-sm font-black text-white shadow-md shadow-[#8D7456]/25 transition hover:bg-[#725D45]"
                            >
                                عرض ملفي الطبي (قراءة)
                            </a>
                        @endif
                        @if ($profileEditUrl)
                            <a
                                href="{{ $profileEditUrl }}"
                                wire:navigate
                                class="inline-flex items-center justify-center rounded-xl border-2 border-[#8D7456] bg-[#FDFBF7] px-4 py-3 text-center text-sm font-black text-[#8D7456] transition hover:bg-[#F5EFE6] dark:bg-gray-900 dark:hover:bg-gray-800"
                            >
                                تعديل الملف الكامل (كل التبويبات)
                            </a>
                        @endif
                        <a
                            href="{{ route('patient.profile.edit') }}"
                            wire:navigate.ignore
                            class="inline-flex items-center justify-center rounded-xl border-2 border-[#8D7456] bg-white px-4 py-3 text-center text-sm font-black text-[#8D7456] transition hover:bg-[#F5EFE6] dark:bg-gray-900 dark:hover:bg-gray-800"
                        >
                            تعديل بياناتي (صفحة مبسطة)
                        </a>
                        <a
                            href="{{ $shareUrl }}"
                            wire:navigate.ignore
                            class="inline-flex items-center justify-center rounded-xl border-2 border-[#8D7456] px-4 py-3 text-center text-sm font-black text-[#8D7456] transition hover:bg-[#fcfaf2] dark:hover:bg-white/5"
                        >
                            إنشاء رابط للطبيب
                        </a>
                        <p class="text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                            الرابط الموقّع يعرض ملخصاً آمناً ولمدة محدودة — لا تنشره علناً.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-[#f0ebe3] bg-[#fcfaf2] px-6 py-4 dark:border-white/10 dark:bg-gray-950 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-semibold text-[#6D5F52] dark:text-gray-400">
                        يمكنك العودة لهذه اللوحة لاحقاً عبر «دخول المريض» أو حفظ الرابط أعلاه.
                    </p>
                    <a
                        href="{{ url('/') }}"
                        wire:navigate.ignore
                        class="inline-flex shrink-0 items-center justify-center rounded-xl border border-[#8D7456] px-4 py-2 text-xs font-black text-[#8D7456] hover:bg-white dark:hover:bg-gray-900"
                    >
                        الانتقال إلى الموقع العام
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
