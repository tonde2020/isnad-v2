<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بياناتي | إسناد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #FDFBF7; }
        .info-card { background-color: #ffffff; border: 1px solid #EAE3D2; border-radius: 1.25rem; }
    </style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col pb-12">
    @include('patient.partials.top-nav', ['active' => 'profile'])

    <main class="flex-1 max-w-2xl mx-auto px-4 py-10 w-full">
        <h1 class="text-2xl font-black text-[#4A3F35] mb-2">بياناتي في الملف الطبي</h1>
        <p class="text-sm text-[#6D5F52] font-medium mb-6">ما تحفظه هنا يظهر في الملف الذي يطلع عليه الطبيب عندما تشاركه برابط مؤقت.</p>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm font-semibold">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('patient.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] border-b border-[#F5EFE6] pb-2">الحساب والاسم</h2>
                @if (filled($patient->registry_number))
                    <div class="rounded-xl border border-[#EAE3D2] bg-[#fcfaf2] px-4 py-3">
                        <p class="text-xs font-black text-[#8D7456] mb-1">رقم السجل</p>
                        <p class="font-mono font-black text-[#4A3F35] text-lg tracking-wide select-all">{{ $patient->registry_number }}</p>
                        <p class="text-[11px] text-[#6D5F52] mt-1">رقم ثابت يعرّف ملفك؛ احتفظ به عند التواصل مع الدعم أو الطبيب.</p>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">الاسم الظاهر في الترحيب</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">الاسم الكامل في الملف الطبي</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $patient->full_name) }}" required class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                </div>
                <p class="text-xs text-slate-500">البريد وكلمة المرور يُغيّران لاحقاً من إعدادات خاصة إن احتجناها؛ حالياً تواصل مع الدعم لتغيير البريد.</p>
            </div>

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] border-b border-[#F5EFE6] pb-2">بيانات أساسية</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" required class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الرقم الوطني</label>
                        <input type="text" name="national_id" value="{{ old('national_id', $patient->national_id) }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">تاريخ الميلاد</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $patient->birth_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">فصيلة الدم</label>
                        <select name="blood_type" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                            <option value="">—</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}" @selected(old('blood_type', $patient->blood_type) === $bt)>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">الجنس</label>
                    <select name="gender" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                        <option value="">—</option>
                        <option value="male" @selected(old('gender', $patient->gender) === 'male')>ذكر</option>
                        <option value="female" @selected(old('gender', $patient->gender) === 'female')>أنثى</option>
                        <option value="unspecified" @selected(old('gender', $patient->gender) === 'unspecified')>يفضّل عدم التحديد</option>
                    </select>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الولاية</label>
                        <input type="text" name="state" value="{{ old('state', $patient->state) }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">المحلية</label>
                        <input type="text" name="locality" value="{{ old('locality', $patient->locality) }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">منطقة النزح / الإقامة</label>
                    <input type="text" name="displacement_area" value="{{ old('displacement_area', $patient->displacement_area) }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                </div>
            </div>

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] border-b border-[#F5EFE6] pb-2">جهة اتصال للطوارئ</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الاسم</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الهاتف</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                    </div>
                </div>
            </div>

            @php
                $oldChronicIds = array_map('intval', old('chronic_master_ids', $selectedChronicMasterIds));
                $oldAllergyIds = array_map('intval', old('allergy_master_ids', $selectedAllergyMasterIds));
                $chronicAllergyMaster = $quickPickMasters->get('chronic_allergy');
            @endphp

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] border-b border-[#F5EFE6] pb-2">الأمراض المزمنة</h2>
                <p class="text-xs text-[#6D5F52]">اختر من القائمة الموحدة؛ الأكثر شيوعاً كاختصار في الأعلى، وباقي التشخيصات من البحث.</p>

                <div class="flex flex-wrap gap-2">
                    @foreach ($quickPickOrder as $pick)
                        @php
                            $qm = $quickPickMasters->get($pick['code']);
                        @endphp
                        @continue($qm === null)
                        @php
                            $isAllergyPick = ! empty($pick['allergy']);
                            $isOn = $isAllergyPick ? in_array((int) $qm->id, $oldAllergyIds, true) : in_array((int) $qm->id, $oldChronicIds, true);
                        @endphp
                        <button type="button"
                            class="quick-pick rounded-full border px-3 py-1.5 text-sm font-bold transition-colors {{ $isOn ? 'border-[#8D7456] bg-[#8D7456] text-white' : 'border-[#EAE3D2] bg-[#FDFBF7] text-[#4A3F35]' }}"
                            data-master-id="{{ $qm->id }}"
                            data-allergy="{{ $isAllergyPick ? '1' : '0' }}"
                            aria-pressed="{{ $isOn ? 'true' : 'false' }}">
                            {{ $pick['label'] }}
                        </button>
                    @endforeach
                </div>

                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">بحث في القائمة</label>
                    <input type="search" id="chronic-disease-search" placeholder="اكتب جزءاً من الاسم…" autocomplete="off" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                </div>

                <div class="max-h-64 overflow-y-auto rounded-xl border border-[#EAE3D2] bg-[#FDFBF7] p-3 space-y-2" id="chronic-master-list">
                    @foreach ($chronicMasters as $m)
                        @php
                            $searchBlob = mb_strtolower($m->name_ar.' '.($m->name_en ?? '').' '.($m->code ?? ''), 'UTF-8');
                        @endphp
                        <label class="chronic-row flex items-start gap-3 rounded-lg px-2 py-2 hover:bg-white cursor-pointer" data-search="{{ e($searchBlob) }}">
                            <input type="checkbox" name="chronic_master_ids[]" value="{{ $m->id }}" id="chronic-{{ $m->id }}"
                                class="mt-1 rounded border-[#EAE3D2] text-[#8D7456] focus:ring-[#8D7456]"
                                @checked(in_array((int) $m->id, $oldChronicIds, true))>
                            <span class="text-sm font-semibold text-[#4A3F35] leading-snug">{{ $m->name_ar }}@if(filled($m->name_en))<span class="text-slate-500 font-normal mr-1"> — {{ $m->name_en }}</span>@endif</span>
                        </label>
                    @endforeach
                </div>

                @if ($chronicAllergyMaster)
                    <input type="checkbox" name="allergy_master_ids[]" value="{{ $chronicAllergyMaster->id }}" id="allergy-{{ $chronicAllergyMaster->id }}" class="sr-only peer"
                        @checked(in_array((int) $chronicAllergyMaster->id, $oldAllergyIds, true))>
                @endif
            </div>

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] border-b border-[#F5EFE6] pb-2">ملاحظات طبية تكتبها بنفسك</h2>
                <p class="text-xs text-[#6D5F52]">أنت المسؤول عن صحة ما تكتب. الطبيب يستخدمها كمساعدة فقط وليست تشخيصاً آلياً.</p>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">تفاصيل إضافية عن الأمراض المزمنة (اختياري)</label>
                    <textarea name="chronic_diseases" rows="3" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">{{ old('chronic_diseases', $patient->chronic_diseases) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">حساسية / تنبيهات (نص حر)</label>
                    <textarea name="allergies" rows="4" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">{{ old('allergies', $patient->allergies) }}</textarea>
                </div>
            </div>

            <script>
                (function () {
                    var search = document.getElementById('chronic-disease-search');
                    var list = document.getElementById('chronic-master-list');
                    if (search && list) {
                        search.addEventListener('input', function () {
                            var q = (search.value || '').trim().toLowerCase();
                            list.querySelectorAll('.chronic-row').forEach(function (row) {
                                var hay = (row.getAttribute('data-search') || '').toLowerCase();
                                row.style.display = q === '' || hay.indexOf(q) !== -1 ? '' : 'none';
                            });
                        });
                    }
                    document.querySelectorAll('.quick-pick').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var id = btn.getAttribute('data-master-id');
                            var allergy = btn.getAttribute('data-allergy') === '1';
                            var input = document.getElementById(allergy ? 'allergy-' + id : 'chronic-' + id);
                            if (!input) return;
                            input.checked = !input.checked;
                            var on = input.checked;
                            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                            btn.classList.toggle('border-[#8D7456]', on);
                            btn.classList.toggle('bg-[#8D7456]', on);
                            btn.classList.toggle('text-white', on);
                            btn.classList.toggle('border-[#EAE3D2]', !on);
                            btn.classList.toggle('bg-[#FDFBF7]', !on);
                            btn.classList.toggle('text-[#4A3F35]', !on);
                        });
                    });
                })();
            </script>

            <button type="submit" class="w-full bg-[#8D7456] text-white py-4 rounded-xl font-black text-lg hover:bg-[#725D45] transition-colors shadow-lg shadow-[#8D7456]/25">
                حفظ التعديلات
            </button>
        </form>
    </main>
</body>
</html>
