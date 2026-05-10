<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعلام برقم السجل | إسناد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; background-color: #FDFBF7; }</style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col pb-12">
    <header class="bg-white/90 border-b border-[#EAE3D2] px-4 py-4">
        <div class="max-w-md mx-auto flex justify-between items-center gap-3 flex-wrap">
            <a href="{{ url('/') }}" class="text-xl font-black text-[#5C4D3C] hover:opacity-90">إسناد</a>
            <span class="text-xs font-bold text-[#8D7456]">استعلام عام — عرض فقط</span>
        </div>
    </header>

    <main class="flex-1 flex items-start justify-center px-4 py-12">
        <div class="max-w-md w-full bg-white border border-[#EAE3D2] rounded-3xl p-8 shadow-sm space-y-6">
            <div>
                <h1 class="text-2xl font-black text-[#4A3F35] mb-2">عرض ملف برقم السجل</h1>
                <p class="text-sm text-[#6D5F52] leading-relaxed">
                    أدخل <strong>رقم السجل</strong> الظاهر في ملف المريض (مثل {{ config('isnad.registry.prefix', 'ISN') }}-00000001). يُعرض الملف للقراءة فقط ضمن جلسة محدودة الزمن.
                </p>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-950 leading-relaxed">
                هذا الاستعلام العام لا يُغني عن مشاركة الطبيب لرابط موقّع خاص. لا تُدخل أرقاماً أمام الآخرين على أجهزة عامة.
            </div>

            @if ($errors->any())
                <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold px-4 py-3">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (! empty($status))
                <div class="rounded-xl bg-sky-50 border border-sky-200 text-sky-900 text-sm font-semibold px-4 py-3">
                    {{ $status }}
                </div>
            @endif

            <form method="post" action="{{ route('patient.registry.lookup.submit') }}" class="space-y-4">
                @csrf
                {{-- حقل فخ للبوتات — يجب أن يبقى فارغاً --}}
                <div class="absolute -left-[9999px] top-auto h-0 w-0 overflow-hidden opacity-0" aria-hidden="true">
                    <label for="website">لا تملأ هذا الحقل</label>
                    <input type="text" name="website" id="website" value="" tabindex="-1" autocomplete="off">
                </div>
                <div>
                    <label for="registry_number" class="block text-sm font-bold text-[#6D5F52] mb-1">رقم السجل</label>
                    <input
                        type="text"
                        name="registry_number"
                        id="registry_number"
                        value="{{ old('registry_number') }}"
                        required
                        autocomplete="off"
                        dir="ltr"
                        class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] font-mono font-bold text-[#4A3F35] tracking-wide focus:ring-2 focus:ring-[#8D7456]/40 outline-none text-center sm:text-left"
                        placeholder="{{ config('isnad.registry.prefix', 'ISN') }}-00000001"
                    >
                </div>
                <div>
                    <label for="captcha_answer" class="block text-sm font-bold text-[#6D5F52] mb-1">
                        تحقق بشري: كم مجموع {{ $captchaA }} + {{ $captchaB }} ؟
                    </label>
                    <input
                        type="number"
                        name="captcha_answer"
                        id="captcha_answer"
                        value="{{ old('captcha_answer') }}"
                        required
                        inputmode="numeric"
                        autocomplete="off"
                        min="0"
                        max="999"
                        class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] font-black text-[#4A3F35] text-lg tracking-wide focus:ring-2 focus:ring-[#8D7456]/40 outline-none"
                        placeholder="الإجابة برقم"
                    >
                    <p class="text-[11px] text-slate-500 mt-1">يحدّث السؤال في كل زيارة للصفحة لتقليل المحاولات الآلية.</p>
                </div>
                <button type="submit" class="w-full bg-[#8D7456] text-white py-3 rounded-xl font-black hover:bg-[#725D45] transition-colors shadow-md shadow-[#8D7456]/25">
                    عرض الملف
                </button>
            </form>

            <p class="text-xs text-slate-400 text-center leading-relaxed">
                بعد النجاح تُفتح صفحة العرض؛ إذا انتهت الجلسة ارجع هنا وأعد إدخال الرقم.
            </p>
        </div>
    </main>
</body>
</html>
