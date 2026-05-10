<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بروفايل المريض | إسناد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #FDFBF7; }
        .info-card {
            background-color: #ffffff;
            border: 1px solid #EAE3D2;
            border-radius: 1.5rem;
        }
    </style>
</head>
<body class="pb-12 text-slate-800">

    @php
        $registryLookupExpiresAt = $registryLookupExpiresAt ?? null;
        $viaRegistryLookup = $viaRegistryLookup ?? false;
        $expiryForTimer = $linkExpiresAt ?? $registryLookupExpiresAt;
    @endphp

    <div class="bg-[#8D7456] text-white py-4 px-6 sticky top-0 z-50 shadow-lg">
        <div class="max-w-6xl mx-auto flex justify-between items-center gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="font-bold">إسناد | ملف طبي مؤقت</span>
            </div>
            @if($expiryForTimer)
                <div class="bg-white/20 px-3 py-1 rounded-full text-xs font-medium backdrop-blur-sm flex items-center gap-2">
                    <span>{{ $viaRegistryLookup ? 'تنتهي جلسة العرض:' : 'صلاحية الرابط:' }}</span>
                    <span id="link-expiry-timer" class="font-mono font-black">--:--</span>
                </div>
            @elseif($viaRegistryLookup)
                <div class="bg-white/15 px-3 py-1 rounded-full text-xs font-medium">عرض برقم السجل</div>
            @else
                <div class="bg-white/15 px-3 py-1 rounded-full text-xs font-medium">رابط موقّع</div>
            @endif
        </div>
    </div>

    @if($viaRegistryLookup)
        <div class="bg-amber-50 border-b border-amber-200 text-amber-950 text-center text-sm font-semibold py-2.5 px-4">
            تم فتح هذا الملف بإدخال <strong>رقم السجل</strong> من الصفحة العامة — للاطلاع فقط. لا يُستبدل هذا الرابط الآمن الموقّع الذي يشاركه المريض للطبيب بنفسه.
        </div>
    @endif

    @php
        $parts = preg_split('/\s+/u', trim((string) $patient->full_name), -1, PREG_SPLIT_NO_EMPTY);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $initials .= mb_substr($p, 0, 1);
        }
        $initials = $initials !== '' ? mb_strtoupper($initials) : '؟';

        $allergyRows = $patient->patientAllergyRecords;
        $showAllergyBox = $allergyRows->isNotEmpty() || filled($patient->allergies);

        $pmActive = $patient->patientMedications->where('is_active', true);
        $legacyActive = $patient->medications->where('is_active', true);
        $hasMed = $pmActive->isNotEmpty() || $legacyActive->isNotEmpty();

        $hasEmergency = filled($patient->emergency_contact_name) || filled($patient->emergency_contact_phone);
    @endphp

    @if($expiryForTimer)
        <script>window.ISNAD_LINK_EXPIRES_AT = {{ $expiryForTimer->timestamp }};</script>
    @endif

    <main class="max-w-6xl mx-auto px-4 sm:px-6 mt-8 lg:mt-10 pb-4">
        @php($qrProfileUrl = $qrProfileUrl ?? request()->fullUrl())
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-start">

            {{-- عمود جانبي: الهوية، QR، العمر، فصيلة الدم، الطوارئ، PDF --}}
            <div class="space-y-6 lg:sticky lg:top-24">
                <div class="bg-white border border-[#EAE3D2] rounded-[2rem] p-6 sm:p-8 shadow-sm">
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 bg-[#F5EFE6] rounded-3xl mx-auto flex items-center justify-center text-3xl text-[#8D7456] font-black border border-[#EAE3D2] mb-4 tracking-tight" aria-hidden="true">
                            {{ $initials }}
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-[#4A3F35] break-words leading-snug">{{ $patient->full_name }}</h1>
                        @if(filled($patient->registry_number))
                            <p class="mt-2 font-mono text-xs sm:text-sm font-black text-[#8D7456]">رقم السجل: {{ $patient->registry_number }}</p>
                        @endif
                    </div>

                    <div class="bg-[#FDFBF7] p-4 rounded-2xl border border-dashed border-[#EAE3D2] mb-6 flex flex-col items-center" role="img" aria-label="رمز استجابة سريعة يفتح نفس صفحة الملف الحالية">
                        <div class="w-[8.5rem] h-[8.5rem] bg-white p-2 rounded-lg shadow-inner flex items-center justify-center overflow-hidden [&_svg]:block [&_svg]:max-w-full [&_svg]:h-auto">
                            {!! QrCode::format('svg')->size(120)->margin(1)->color(141, 116, 86)->backgroundColor(255, 255, 255)->generate($qrProfileUrl) !!}
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold mt-2 uppercase tracking-widest text-center leading-snug px-1">المسح للوصول السريع لهذا العرض</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 border-t border-[#F5EFE6] pt-6">
                        <div class="text-center p-3 bg-[#FDFBF7] rounded-2xl border border-[#F5EFE6]">
                            <span class="block text-[11px] text-slate-400 font-bold mb-1">العمر</span>
                            <span class="font-black text-[#4A3F35] text-sm sm:text-base">
                                @if($patient->birth_date)
                                    {{ $patient->birth_date->age }} سنة
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="text-center p-3 rounded-2xl border {{ $patient->blood_type ? 'bg-red-50 border-red-100' : 'bg-slate-50 border-slate-200' }}">
                            <span class="block text-[11px] {{ $patient->blood_type ? 'text-red-400' : 'text-slate-400' }} font-bold mb-1">فصيلة الدم</span>
                            <span class="font-black {{ $patient->blood_type ? 'text-red-600 text-lg' : 'text-slate-500 text-sm' }}">{{ $patient->blood_type ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    @if($hasEmergency)
                        <div class="bg-[#F5EFE6]/60 border border-[#EAE3D2] rounded-[2rem] p-6 shadow-sm">
                            <h2 class="text-base font-black text-[#8D7456] mb-4 flex items-center gap-2 justify-center sm:justify-start">
                                <svg class="w-5 h-5 shrink-0 text-[#8D7456]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                جهة اتصال الطوارئ
                            </h2>
                            <dl class="space-y-3 text-sm">
                                @if(filled($patient->emergency_contact_name))
                                    <div class="flex justify-between gap-3">
                                        <span class="text-slate-500 font-bold shrink-0">الاسم</span>
                                        <span class="text-[#4A3F35] font-black text-left">{{ $patient->emergency_contact_name }}</span>
                                    </div>
                                @endif
                                @if(filled($patient->emergency_contact_phone))
                                    <div class="flex justify-between gap-3">
                                        <span class="text-slate-500 font-bold shrink-0">الهاتف</span>
                                        <span class="text-[#4A3F35] font-black" dir="ltr">{{ $patient->emergency_contact_phone }}</span>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    <a href="{{ $pdfDownloadUrl }}" target="_blank" rel="noopener" class="w-full bg-[#8D7456] text-white py-4 rounded-2xl font-black hover:bg-[#725D45] transition-all flex items-center justify-center gap-3 shadow-lg shadow-[#8D7456]/20 no-underline">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        تحميل نسخة PDF
                    </a>
                </div>
            </div>

            {{-- المحتوى الطبي الرئيسي --}}
            <div class="lg:col-span-2 space-y-8 min-w-0">

                @if($showAllergyBox)
                    <div class="bg-red-50 border-r-[8px] border-red-500 rounded-2xl p-5 sm:p-6 shadow-sm ring-1 ring-red-100/80" role="alert">
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            <div class="bg-red-500 text-white p-2.5 rounded-xl shrink-0 shadow-md shadow-red-500/25">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-black text-red-900 text-lg sm:text-xl mb-2">تنبيهات الحساسية</h3>
                                @if($allergyRows->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @foreach($allergyRows as $row)
                                            <span class="inline-flex items-center rounded-full bg-red-600 text-white px-3 py-1 text-xs font-black shadow-sm">{{ $row->displayLabel() }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(filled($patient->allergies))
                                    @if($allergyRows->isNotEmpty())
                                        <p class="text-red-800 font-bold text-sm mb-1">ملاحظات إضافية</p>
                                    @endif
                                    <div class="text-red-800 font-semibold leading-relaxed whitespace-pre-wrap">{!! nl2br(e($patient->allergies)) !!}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if(filled($patient->clinical_ai_summary) && ! $patient->ai_summary_disabled)
                    <div class="info-card p-6 border-sky-200 bg-sky-50/70 shadow-sm rounded-3xl">
                        <h2 class="text-lg font-black text-sky-950 mb-2 flex items-center gap-2">
                            <span class="w-1.5 h-6 bg-sky-500 rounded-full shrink-0"></span>
                            ملخص مساعد (ذكاء اصطناعي)
                        </h2>
                        <p class="text-xs text-sky-900/80 font-semibold mb-3">
                            للمساعدة على القراءة السريعة فقط — ليس تشخيصاً ولا توصية علاجية.
                            @if($patient->clinical_ai_summary_generated_at)
                                <span class="block mt-1">تاريخ التوليد: {{ $patient->clinical_ai_summary_generated_at->format('Y-m-d H:i') }}</span>
                            @endif
                        </p>
                        <div class="text-sm text-sky-950 whitespace-pre-wrap leading-relaxed">{!! nl2br(e($patient->clinical_ai_summary)) !!}</div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white border border-[#EAE3D2] rounded-3xl p-6 shadow-sm min-h-[180px] flex flex-col">
                        <h2 class="text-lg font-black text-[#4A3F35] mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-[#8D7456] rounded-full shrink-0"></span>
                            الأمراض المزمنة
                        </h2>
                        @if($patient->patientChronicDiseases->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($patient->patientChronicDiseases as $row)
                                    <span class="bg-gradient-to-br from-[#FDFBF7] to-[#F5EFE6] text-[#6D5F52] px-3 py-2 rounded-xl text-sm font-black border border-[#EAE3D2] shadow-sm">{{ $row->displayLabel() }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if(filled($patient->chronic_diseases))
                            @if($patient->patientChronicDiseases->isNotEmpty())
                                <p class="text-[11px] font-bold text-slate-400 mb-1">تفاصيل إضافية</p>
                            @endif
                            <div class="text-[#6D5F52] font-medium text-sm whitespace-pre-wrap rounded-2xl bg-[#FAF7F2] border border-[#F5EFE6] p-4 flex-1">{!! nl2br(e($patient->chronic_diseases)) !!}</div>
                        @elseif($patient->patientChronicDiseases->isEmpty())
                            <p class="text-slate-400 font-semibold text-sm mt-auto italic">لا توجد أمراض مزمنة مسجلة في السجل المنظم.</p>
                        @endif
                    </div>

                    <div class="bg-white border border-[#EAE3D2] rounded-3xl p-6 shadow-sm min-h-[180px] flex flex-col">
                        <h2 class="text-lg font-black text-[#4A3F35] mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-5 bg-[#8D7456] rounded-full shrink-0"></span>
                            الأدوية الحالية
                        </h2>
                        <div class="space-y-3 flex-1">
                            @foreach($pmActive as $med)
                                <div class="rounded-2xl border border-[#F5EFE6] bg-[#FDFBF7] p-4 flex flex-wrap justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="font-black text-[#4A3F35]">{{ $med->displayMedicationName() }}</h4>
                                        @if($med->dosage || $med->frequency || $med->duration)
                                            <p class="text-xs text-slate-500 font-semibold mt-1">
                                                {{ trim(implode(' — ', array_filter([$med->dosage, $med->frequency, $med->duration]))) }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="bg-emerald-50 text-emerald-800 text-[11px] px-2 py-1 rounded-lg font-black border border-emerald-100 h-fit shrink-0">نشط</span>
                                </div>
                            @endforeach
                            @if($pmActive->isEmpty())
                                @foreach($legacyActive as $med)
                                    <div class="rounded-2xl border border-[#F5EFE6] bg-[#FDFBF7] p-4 flex flex-wrap justify-between gap-3">
                                        <div class="min-w-0">
                                            <h4 class="font-black text-[#4A3F35]">{{ $med->medication_name }}</h4>
                                            @if($med->dosage || $med->frequency)
                                                <p class="text-xs text-slate-500 font-semibold mt-1">{{ trim(implode(' — ', array_filter([$med->dosage, $med->frequency]))) }}</p>
                                            @endif
                                        </div>
                                        <span class="bg-emerald-50 text-emerald-800 text-[11px] px-2 py-1 rounded-lg font-black border border-emerald-100 h-fit shrink-0">نشط</span>
                                    </div>
                                @endforeach
                            @endif
                            @if(! $hasMed)
                                <div class="mt-auto rounded-2xl border border-dashed border-[#EAE3D2] bg-[#FAFAF8] px-4 py-8 text-center">
                                    <p class="text-sm text-slate-400 font-bold italic">لا توجد أدوية نشطة مسجلة حالياً.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-[#EAE3D2] rounded-3xl p-6 sm:p-8 shadow-sm">
                    <h2 class="text-lg font-black text-[#4A3F35] mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-5 bg-[#8D7456] rounded-full shrink-0"></span>
                        آخر التقارير والمرفقات
                    </h2>
                    @if($patient->medicalRecords->isEmpty())
                        <div class="border-2 border-dashed border-[#F0EBE3] rounded-[2rem] py-14 px-6 text-center bg-[#FDFBF7]/50">
                            <svg class="w-16 h-16 mx-auto text-[#E8DFD0] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M5 19a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-4l-2-2H7a2 2 0 00-2 2v14zm4-8h6m-6 4h6m-6-8h2"/>
                            </svg>
                            <p class="text-slate-500 font-bold">لم يُرفق أي ملف طبي حديثاً في هذا العرض.</p>
                            <p class="text-xs text-slate-400 mt-2 font-medium">عند توفر المرفقات تظهر هنا كبطاقات مع إمكانية المعاينة.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($patient->medicalRecords as $rec)
                                <article class="info-card p-3 shadow-sm flex flex-col rounded-2xl overflow-hidden border-[#EAE3D2]">
                                    <div class="aspect-[4/3] bg-gradient-to-b from-[#F5EFE6] to-[#FAF7F2] rounded-xl mb-2 flex flex-col items-center justify-center text-center px-2 border border-[#EAE3D2]">
                                        <svg class="w-8 h-8 text-[#C4B8A8] mb-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="text-[11px] font-black text-[#4A3F35] leading-snug line-clamp-3">{{ $rec->title ?: 'مرفق طبي' }}</span>
                                        @if($rec->processing_status)
                                            <span class="text-[9px] text-slate-400 mt-1 uppercase tracking-wide">{{ $rec->processing_status }}</span>
                                        @endif
                                    </div>
                                    @if($rec->record_date)
                                        <p class="text-[11px] font-black text-center text-[#8D7456] pb-2">{{ $rec->record_date->format('Y-m') }}</p>
                                    @else
                                        <p class="text-[11px] font-black text-center text-[#8D7456] pb-2">بدون تاريخ</p>
                                    @endif
                                    @if($rec->file_path || $rec->enhanced_file_path)
                                        <div class="mt-auto flex flex-col gap-1.5">
                                            <a href="{{ \App\Support\PatientTemporaryProfileLink::medicalRecordFileUrl($patient, $rec) }}" target="_blank" rel="noopener" class="text-center text-[11px] font-black text-white bg-[#8D7456] rounded-lg py-2 hover:bg-[#725D45] transition-colors">عرض الملف</a>
                                            @if($rec->hasDistinctEnhancedFile())
                                                <a href="{{ \App\Support\PatientTemporaryProfileLink::medicalRecordOriginalFileUrl($patient, $rec) }}" target="_blank" rel="noopener" class="text-center text-[10px] font-bold text-[#8D7456] underline-offset-2 hover:underline">الأصل</a>
                                            @endif
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <p class="text-center text-xs sm:text-sm text-slate-400 font-medium max-w-2xl mx-auto leading-relaxed pt-2 border-t border-[#EAE3D2]/80 mt-8">
                    هذا الملف متاح للاطلاع الطبي عبر رابط موقّع أو جلسة عرض محدودة. لا تشاركه مع غير المختصين.
                </p>
            </div>
        </div>
    </main>

    @if($expiryForTimer)
        <script>
            (function () {
                var el = document.getElementById('link-expiry-timer');
                if (!el || typeof window.ISNAD_LINK_EXPIRES_AT !== 'number') return;
                var deadline = window.ISNAD_LINK_EXPIRES_AT * 1000;
                function tick() {
                    var ms = deadline - Date.now();
                    if (ms <= 0) {
                        el.textContent = 'منتهية';
                        return;
                    }
                    var totalSec = Math.floor(ms / 1000);
                    var m = Math.floor(totalSec / 60);
                    var s = totalSec % 60;
                    el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                }
                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endif
</body>
</html>
