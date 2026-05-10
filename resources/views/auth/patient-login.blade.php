<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول المريض | إسناد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; background-color: #FDFBF7; }</style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col">
    <header class="bg-white/90 border-b border-[#EAE3D2] px-4 py-4">
        <div class="max-w-md mx-auto flex justify-between items-center">
            <a href="{{ url('/') }}" class="text-xl font-black text-[#5C4D3C]">إسناد</a>
            <a href="{{ route('patient.register') }}" class="text-sm font-bold text-[#8D7456]">تسجيل جديد</a>
        </div>
    </header>

    <main class="flex-1 flex items-center px-4 py-12">
        <div class="max-w-md mx-auto w-full bg-white border border-[#EAE3D2] rounded-3xl p-8 shadow-sm">
            <h1 class="text-2xl font-black text-[#4A3F35] mb-2">دخول المريض</h1>
            <p class="text-sm text-[#6D5F52] mb-2">حساب منفصل عن لوحة فريق الرعاية.</p>
            <p class="text-xs text-[#8D7456] font-bold mb-6 leading-relaxed">بعد إدخال البريد وكلمة المرور والضغط على الزر أدناه، ستُفتح لك <strong>لوحتي</strong> (ملفك الطبي) مباشرة.</p>

            <div class="mb-6">
                <a href="{{ url('/app') }}" class="block w-full text-center text-sm font-black text-[#8D7456] border-2 border-dashed border-[#8D7456]/40 hover:bg-[#F5EFE6] px-4 py-3 rounded-xl transition-colors">
                    لدي حساب وتظهر لي صفحة الدخول بالخطأ → جرّب فتح لوحتي
                </a>
                <p class="text-[10px] text-slate-400 mt-2 text-center">إن لم تكن مسجّلاً الدخول، سيطلب منك النظام تسجيل الدخول أولاً.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold px-4 py-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('patient.login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#6D5F52] mb-1">كلمة المرور</label>
                    <input type="password" name="password" required autocomplete="current-password" class="w-full rounded-xl border border-[#EAE3D2] px-4 py-3 bg-[#FDFBF7] focus:ring-2 focus:ring-[#8D7456]/40 outline-none">
                </div>
                <label class="flex items-center gap-2 text-sm font-medium text-[#6D5F52]">
                    <input type="checkbox" name="remember" value="1" class="rounded border-[#EAE3D2] text-[#8D7456] focus:ring-[#8D7456]">
                    تذكرني على هذا الجهاز
                </label>
                <button type="submit" class="w-full bg-[#8D7456] text-white py-3 rounded-xl font-black hover:bg-[#725D45] transition-colors shadow-md shadow-[#8D7456]/25">دخول لوحتي</button>
            </form>

            <p class="text-xs text-slate-400 text-center mt-6">
                فريق الرعاية (طبيب/موظف):
                <a href="{{ url('/app/login') }}" class="text-[#8D7456] font-bold underline-offset-2 hover:underline">دخول حساب الفريق</a>
            </p>
        </div>
    </main>
</body>
</html>
