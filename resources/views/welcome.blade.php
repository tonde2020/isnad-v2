<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إسناد | ملفك الطبي معك أينما كنت</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Cairo', sans-serif; 
            background-color: #FDFBF7; /* خلفية كريمي فاتحة جداً */
        }
        .bg-cream-dark { background-color: #F5EFE6; }
        .bg-brand-primary { background-color: #C2A383; } /* لون كريمي غامق للبراند */
        .text-brand-primary { color: #8D7456; }
        .hero-section {
            background: linear-gradient(135deg, #F5EFE6 0%, #EAE3D2 100%);
        }
        .btn-primary {
            background-color: #8D7456;
            color: #ffffff;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #725D45;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="text-slate-800">

    <nav class="bg-white/90 backdrop-blur-md sticky top-0 z-50 border-b border-[#EAE3D2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-[#8D7456] rounded-lg flex items-center justify-center shadow-lg shadow-[#8D7456]/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <span class="text-2xl font-black text-[#5C4D3C] tracking-tight">إسناد</span>
                </div>
                <div class="hidden md:flex items-center gap-6 lg:gap-8 text-[#8D7456] font-semibold flex-wrap justify-center max-w-xl">
                    <a href="#features" class="hover:text-[#5C4D3C] transition-colors">المميزات</a>
                    <a href="#how-it-works" class="hover:text-[#5C4D3C] transition-colors">كيف يعمل؟</a>
                    <a href="#security" class="hover:text-[#5C4D3C] transition-colors">الأمان</a>
                    <a href="{{ route('patient.registry.lookup') }}" class="font-black text-[#6D5F52] hover:text-[#4A3F35] transition-colors border-b-2 border-transparent hover:border-[#8D7456]/40 pb-0.5">استعلام برقم السجل</a>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-end min-w-0">
                    @auth
                        <a href="{{ route('patient.registry.lookup') }}" class="text-xs sm:text-sm font-black text-[#8D7456] hover:text-[#5C4D3C] px-2 sm:px-3 py-2 whitespace-nowrap underline-offset-2 hover:underline shrink-0">عرض برقم السجل</a>
                        <a href="{{ url('/app') }}" class="btn-primary px-4 sm:px-6 py-2.5 rounded-full font-bold shadow-md shadow-[#8D7456]/20 text-sm sm:text-base shrink-0">لوحتي</a>
                    @else
                        <a
                            href="{{ route('patient.register') }}"
                            onclick="return confirm('بتسجيلك تؤكد أنك قرأت وأنك تتحمل مسؤولية صحة البيانات التي تدخلها بنفسك، وأن المنصة لا تتحقق منها تلقائياً. هل تريد المتابعة؟');"
                            class="text-sm sm:text-base font-bold text-[#8D7456] hover:text-[#5C4D3C] px-3 py-2 rounded-full border border-[#EAE3D2] bg-white/80 hover:bg-white transition-colors"
                        >تسجيل مريض</a>
                        <a href="{{ route('patient.login') }}" class="text-sm sm:text-base font-bold text-[#6D5F52] hover:text-[#4A3F35] px-3 py-2">دخول المريض</a>
                        <a href="{{ route('patient.registry.lookup') }}" class="text-sm sm:text-base font-bold text-[#8D7456] hover:text-[#5C4D3C] px-3 py-2 underline-offset-2 hover:underline">عرض برقم السجل</a>
                        <a href="{{ url('/app/login') }}" class="btn-primary px-5 sm:px-6 py-2.5 rounded-full font-bold shadow-md shadow-[#8D7456]/20 text-sm sm:text-base">دخول الفريق</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section py-20 lg:py-32 relative overflow-hidden border-b border-[#EAE3D2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-right">
                    <span class="inline-block px-4 py-1 rounded-full bg-[#8D7456]/10 text-[#8D7456] text-sm font-bold mb-6 border border-[#8D7456]/20">مبادرة إنسانية لاستمرارية العلاج</span>
                    <h1 class="text-5xl lg:text-6xl font-extrabold leading-tight mb-6 text-[#4A3F35]">
                        تاريخك الطبي.. <br><span class="text-[#8D7456]">أمانٌ تحمله معك</span>
                    </h1>
                    <p class="text-xl text-[#6D5F52] mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0 font-medium">
                        أنت تبني ملفك من لوحة المريض؛ الطبيب يطلع عليه فقط عندما تشاركه برابط مؤقت موقّع — خصوصية أولاً.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start flex-wrap">
                        <a
                            href="{{ route('patient.register') }}"
                            onclick="return confirm('بتسجيلك تؤكد أنك قرأت وأنك تتحمل مسؤولية صحة البيانات التي تدخلها بنفسك، وأن المنصة لا تتحقق منها تلقائياً. هل تريد المتابعة؟');"
                            class="inline-flex items-center justify-center px-8 py-4 btn-primary rounded-xl font-bold text-lg text-center"
                        >تسجيل كمريض</a>
                        <a href="{{ route('patient.login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white/50 hover:bg-white text-[#8D7456] border border-[#EAE3D2] rounded-xl font-bold text-lg transition-all shadow-sm">دخول لوحة المريض</a>
                        <a href="#features" class="inline-flex items-center justify-center px-8 py-4 bg-white/50 hover:bg-white text-[#8D7456] border border-[#EAE3D2] rounded-xl font-bold text-lg transition-all shadow-sm">استكشف المميزات</a>
                    </div>
                </div>
                <div class="hidden lg:block relative">
                    <div class="relative w-full h-[480px] bg-white rounded-[2rem] border border-[#EAE3D2] shadow-2xl p-6 transform hover:rotate-0 transition-transform duration-700 rotate-2">
                        <div class="flex items-center gap-4 mb-8 border-b border-[#F5EFE6] pb-4">
                            <div class="w-14 h-14 bg-[#F5EFE6] rounded-2xl flex items-center justify-center text-[#8D7456] font-bold text-xl">م</div>
                            <div class="space-y-1">
                                <div class="h-4 w-32 bg-[#F5EFE6] rounded"></div>
                                <div class="h-3 w-20 bg-[#FAF7F2] rounded"></div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="bg-[#FDFBF7] border border-[#F5EFE6] rounded-2xl p-4">
                                <div class="h-3 w-1/3 bg-[#EAE3D2] rounded mb-3"></div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="h-2 bg-[#F5EFE6] rounded"></div>
                                    <div class="h-2 bg-[#F5EFE6] rounded"></div>
                                    <div class="h-2 bg-[#F5EFE6] rounded"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="h-24 bg-[#FAF7F2] rounded-2xl border border-[#F5EFE6]"></div>
                                <div class="h-24 bg-[#FAF7F2] rounded-2xl border border-[#F5EFE6]"></div>
                            </div>
                        </div>
                        <div class="absolute -bottom-6 -left-6 bg-[#8D7456] text-white p-6 rounded-2xl shadow-xl">
                            <div class="text-sm opacity-80">تم فتح الرابط بواسطة:</div>
                            <div class="font-bold">د. أحمد علي - منذ دقيقتين</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-20 bg-white border-y border-[#EAE3D2]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-black text-[#4A3F35] mb-3">كيف يعمل؟</h2>
                <p class="text-[#6D5F52] max-w-2xl mx-auto">ثلاث خطوات بسيطة تربط المريض بالطبيب دون تعقيد.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div class="p-6 rounded-2xl bg-[#FDFBF7] border border-[#F5EFE6]">
                    <div class="text-3xl font-black text-[#8D7456] mb-2">١</div>
                    <h3 class="font-bold text-lg text-[#4A3F35] mb-2">المريض يدير ملفه</h3>
                    <p class="text-[#6D5F52] text-sm leading-relaxed">التسجيل وتحديث البيانات الأساسية والملاحظات الطبية التي يكتبها المريض من لوحة المريض.</p>
                </div>
                <div class="p-6 rounded-2xl bg-[#FDFBF7] border border-[#F5EFE6]">
                    <div class="text-3xl font-black text-[#8D7456] mb-2">٢</div>
                    <h3 class="font-bold text-lg text-[#4A3F35] mb-2">رابط للطبيب عند الرغبة</h3>
                    <p class="text-[#6D5F52] text-sm leading-relaxed">المريض يُنشئ رابطاً مؤقتاً موقّعاً ومحدود الزمن ويشاركه مع الطبيب فقط عندما يريد ذلك.</p>
                </div>
                <div class="p-6 rounded-2xl bg-[#FDFBF7] border border-[#F5EFE6]">
                    <div class="text-3xl font-black text-[#8D7456] mb-2">٣</div>
                    <h3 class="font-bold text-lg text-[#4A3F35] mb-2">اطلاع الطبيب</h3>
                    <p class="text-[#6D5F52] text-sm leading-relaxed">الطبيب لا يرى الملف بدون الرابط؛ يفتح الرابط ويطّلع على الملخص المعروض دون تسجيل إضافي.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-3xl lg:text-4xl font-black text-[#4A3F35] mb-4">بساطة في التعامل، دقة في التفاصيل</h2>
                <div class="w-20 h-1.5 bg-[#8D7456] mx-auto rounded-full"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-10">
                <div class="p-10 bg-white rounded-[2.5rem] border border-[#F5EFE6] hover:border-[#8D7456]/30 transition-all shadow-sm hover:shadow-xl group">
                    <div class="w-16 h-16 bg-[#F5EFE6] text-[#8D7456] rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-[#4A3F35] mb-4">رابط وصول ذكي</h3>
                    <p class="text-[#6D5F52] leading-relaxed font-medium">أرسل رابطاً مشفراً لطبيبك ينتهي تلقائياً، دون الحاجة لتحميل تطبيقات أو كلمات مرور.</p>
                </div>
                <div class="p-10 bg-white rounded-[2.5rem] border border-[#F5EFE6] hover:border-[#8D7456]/30 transition-all shadow-sm hover:shadow-xl group">
                    <div class="w-16 h-16 bg-[#F5EFE6] text-[#8D7456] rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-[#4A3F35] mb-4">أرشفة الأدوية</h3>
                    <p class="text-[#6D5F52] leading-relaxed font-medium">سجل أدويتك الحالية وجرعاتها ليطلع عليها الطبيب بوضوح ويضمن عدم حدوث تداخلات دوائية.</p>
                </div>
                <div class="p-10 bg-white rounded-[2.5rem] border border-[#F5EFE6] hover:border-[#8D7456]/30 transition-all shadow-sm hover:shadow-xl group">
                    <div class="w-16 h-16 bg-[#F5EFE6] text-[#8D7456] rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-[#4A3F35] mb-4">خصوصية مطلقة</h3>
                    <p class="text-[#6D5F52] leading-relaxed font-medium">بياناتك ملكك وحدك. تستطيع إلغاء صلاحية أي رابط أو حذف بياناتك في أي وقت تشاء.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="security" class="py-20 bg-[#FDFBF7] border-y border-[#EAE3D2]">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-black text-[#4A3F35] mb-6">الأمان والخصوصية</h2>
            <ul class="text-right space-y-4 text-[#6D5F52] font-medium leading-relaxed">
                <li class="flex gap-3 items-start justify-end"><span class="text-[#8D7456] shrink-0">✓</span><span>روابط الوصول <strong class="text-[#4A3F35]">موقّعة رقمياً</strong> ولا تعمل بدون التحقق من الخادم.</span></li>
                <li class="flex gap-3 items-start justify-end"><span class="text-[#8D7456] shrink-0">✓</span><span>صلاحية الرابط <strong class="text-[#4A3F35]">محدودة بالوقت</strong> لتقليل مخاطر التسريب.</span></li>
                <li class="flex gap-3 items-start justify-end"><span class="text-[#8D7456] shrink-0">✓</span><span>تسجيل <strong class="text-[#4A3F35]">زمن وعنوان</strong> لكل عملية فتح (قابل للتوسع لاحقاً للتدقيق).</span></li>
            </ul>
        </div>
    </section>

    <section class="py-24 px-4">
        <div class="max-w-5xl mx-auto bg-[#F5EFE6] rounded-[3rem] p-12 lg:p-20 text-center border border-[#EAE3D2] relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-4xl lg:text-5xl font-black text-[#4A3F35] mb-8">ابدأ بتوثيق تاريخك الطبي اليوم</h2>
                <p class="text-xl text-[#6D5F52] mb-12 font-medium">لا تسمح للظروف أن تفقدك السيطرة على صحتك. سجل الآن مجاناً.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center flex-wrap">
                    <a
                        href="{{ route('patient.register') }}"
                        onclick="return confirm('بتسجيلك تؤكد أنك قرأت وأنك تتحمل مسؤولية صحة البيانات التي تدخلها بنفسك، وأن المنصة لا تتحقق منها تلقائياً. هل تريد المتابعة؟');"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-10 py-4 btn-primary rounded-xl font-extrabold text-lg shadow-xl shadow-[#8D7456]/30"
                    >تسجيل مريض</a>
                    <a href="{{ route('patient.login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-10 py-4 bg-white text-[#8D7456] border-2 border-[#8D7456]/40 rounded-xl font-extrabold text-lg hover:bg-[#F5EFE6] transition-colors">دخول لوحة المريض</a>
                    <a href="{{ url('/app/login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-10 py-4 text-[#6D5F52] font-extrabold text-base hover:text-[#4A3F35] underline-offset-4 hover:underline">دخول فريق إسناد</a>
                </div>
                <p class="text-sm text-[#6D5F52]/80 mt-6 max-w-2xl mx-auto leading-relaxed">المريض يحدّث ملفه من لوحة المريض ويُنشئ رابطاً للطبيب عندما يريد؛ لوحة إسناد مخصّصة لفريق الدعم والإدارة السريرية.</p>
            </div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-[#EAE3D2]/50 rounded-full"></div>
        </div>
    </section>

    <footer class="bg-[#4A3F35] text-[#F5EFE6] py-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="text-3xl font-black mb-4">إسناد</div>
            <p class="opacity-70 mb-8 font-medium">منصة سودانية تقنية لدعم استمرارية الخدمات الصحية</p>
            <div class="h-px bg-white/10 w-full mb-8"></div>
            <p class="text-sm opacity-50">© 2026 جميع الحقوق محفوظة لمنصة إسناد</p>
        </div>
    </footer>

</body>
</html>