<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل مريض | إسناد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #FDFBF7; }
        .info-card { background-color: #ffffff; border: 1px solid #EAE3D2; border-radius: 1.25rem; }
    </style>
</head>
<body class="text-slate-800 pb-16">
    <header class="bg-white/90 backdrop-blur border-b border-[#EAE3D2] sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ url('/') }}" class="text-xl font-black text-[#5C4D3C]">إسناد</a>
            <a href="{{ route('patient.login') }}" class="text-sm font-bold text-[#8D7456] hover:text-[#5C4D3C]">لديك حساب؟ دخول</a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 mt-8">
        <h1 class="text-2xl font-black text-[#4A3F35] mb-2">إنشاء حساب مريض</h1>
        <p class="text-[#6D5F52] font-medium mb-6">أدخل بياناتك الأساسية لبدء استخدام المنصة. لا يُطلب التحقق برمز SMS في هذا الإصدار.</p>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm font-semibold" role="alert">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('patient.register.store') }}" class="space-y-6">
            @csrf

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] text-lg border-b border-[#F5EFE6] pb-2">الحساب</h2>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">الاسم الظاهر في الحساب</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">كلمة المرور</label>
                        <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                </div>
            </div>

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] text-lg border-b border-[#F5EFE6] pb-2">البروفايل الطبي الأساسي</h2>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">الاسم الكامل كما في الوثائق</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الهاتف <span class="text-red-600">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الرقم الوطني (اختياري)</label>
                        <input type="text" name="national_id" value="{{ old('national_id') }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">تاريخ الميلاد</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">فصيلة الدم</label>
                        <select name="blood_type" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                            <option value="">—</option>
                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                                <option value="{{ $bt }}" @selected(old('blood_type') === $bt)>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">الجنس</label>
                    <select name="gender" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                        <option value="">—</option>
                        <option value="male" @selected(old('gender') === 'male')>ذكر</option>
                        <option value="female" @selected(old('gender') === 'female')>أنثى</option>
                        <option value="unspecified" @selected(old('gender') === 'unspecified')>يفضّل عدم التحديد</option>
                    </select>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">الولاية</label>
                        <input type="text" name="state" value="{{ old('state') }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">المحلية</label>
                        <input type="text" name="locality" value="{{ old('locality') }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">منطقة النزوح / الإقامة (اختياري)</label>
                    <input type="text" name="displacement_area" value="{{ old('displacement_area') }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                </div>
            </div>

            <div class="info-card p-6 shadow-sm space-y-4">
                <h2 class="font-black text-[#4A3F35] text-lg border-b border-[#F5EFE6] pb-2">جهة اتصال للطوارئ (اختياري)</h2>
                <p class="text-sm text-[#6D5F52]">يُفضّل وجودها ليتمكن الطبيب من التواصل السريع عند الحاجة.</p>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">اسم جهة الاتصال</label>
                        <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-[#6D5F52] mb-1">هاتف جهة الاتصال</label>
                        <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 focus:border-[#8D7456] outline-none">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50/80 p-5 space-y-4">
                <p class="font-bold text-amber-950 text-sm leading-relaxed">
                    المنصة <strong>لا تتحقق تلقائياً</strong> من صحة البيانات التي تُدخلها بنفسك (الاسم، العمر، الحساسية، الأدوية، إلخ).
                    أنت المسؤول عن دقة ما تكتبه؛ استخدام الطبيب لهذه البيانات يكون وفق تقديره المهني دون أن تتحمل المنصة مسؤولية الأخطاء في الإدخال الذاتي.
                </p>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="accept_self_entered_disclaimer" value="1" class="mt-1 rounded border-amber-400 text-[#8D7456] focus:ring-[#8D7456]" {{ old('accept_self_entered_disclaimer') ? 'checked' : '' }} required>
                    <span class="text-sm font-semibold text-amber-950">أقرّ بذلك وأتحمل مسؤولية صحة البيانات التي أدخلها.</span>
                </label>
            </div>

            <button type="submit" class="w-full bg-[#8D7456] text-white py-4 rounded-xl font-black text-lg hover:bg-[#725D45] transition-colors shadow-lg shadow-[#8D7456]/25">
                إنشاء الحساب والبدء
            </button>
        </form>
    </main>
</body>
</html>
