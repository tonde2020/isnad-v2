<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشروعي الجديد - الرئيسية</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <header class="fixed w-full bg-white/80 backdrop-blur-md shadow-sm z-50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-blue-600">لوغو المشروع</div>
            
            <ul class="hidden md:flex space-x-reverse space-x-8 font-medium">
                <li><a href="#" class="hover:text-blue-600 transition">الرئيسية</a></li>
                <li><a href="#features" class="hover:text-blue-600 transition">المميزات</a></li>
                <li><a href="#" class="hover:text-blue-600 transition">عن المنصة</a></li>
            </ul>

            <div>
                <a href="#" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">ابدأ الآن</a>
            </div>
        </nav>
    </header>

    <section class="pt-32 pb-20 px-6">
        <div class="container mx-auto text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                حول أفكارك إلى <span class="text-blue-600">واقع رقمي</span> متكامل
            </h1>
            <p class="text-lg text-gray-600 mb-10 max-w-2xl mx-auto">
                هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، لقد تم توليد هذا النص من مولد النص العربى، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى.
            </p>
            <div class="flex justify-center gap-4">
                <button class="bg-blue-600 text-white px-8 py-3 rounded-xl shadow-lg hover:shadow-blue-200 transition">تواصل معنا</button>
                <button class="bg-white border border-gray-200 px-8 py-3 rounded-xl hover:bg-gray-50 transition">تعرف علينا</button>
            </div>
        </div>
    </section>

    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-12 text-center">
                <div class="p-8 rounded-2xl bg-gray-50 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">⚡</div>
                    <h3 class="text-xl font-bold mb-4">سرعة في التنفيذ</h3>
                    <p class="text-gray-500">نحن نضمن لك أداءً عاليًا وسرعة فائقة في معالجة البيانات والطلبات.</p>
                </div>
                <div class="p-8 rounded-2xl bg-gray-50 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">🔒</div>
                    <h3 class="text-xl font-bold mb-4">أمان البيانات</h3>
                    <p class="text-gray-500">أنظمة حماية متقدمة لضمان خصوصية بيانات المستخدمين والعمليات المالية.</p>
                </div>
                <div class="p-8 rounded-2xl bg-gray-50 hover:bg-blue-50 transition border border-transparent hover:border-blue-100">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">📊</div>
                    <h3 class="text-xl font-bold mb-4">تقارير ذكية</h3>
                    <p class="text-gray-500">لوحة تحكم شاملة تعطيك رؤية واضحة لكل ما يدور في مشروعك بلحظة.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center border-b border-gray-800 pb-8">
                <div class="text-2xl font-bold mb-4 md:mb-0">لوغو المشروع</div>
                <div class="flex space-x-reverse space-x-6 text-gray-400">
                    <a href="#" class="hover:text-white">سياسة الخصوصية</a>
                    <a href="#" class="hover:text-white">الشروط والأحكام</a>
                    <a href="#" class="hover:text-white">الدعم الفني</a>
                </div>
            </div>
            <div class="text-center mt-8 text-gray-500 text-sm">
                &copy; 2026 جميع الحقوق محفوظة لشركتك الناشئة.
            </div>
        </div>
    </footer>

</body>
</html>