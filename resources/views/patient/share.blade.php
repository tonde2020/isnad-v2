<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رابط للطبيب | إسناد</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; background-color: #FDFBF7; }</style>
</head>
<body class="text-slate-800 min-h-screen flex flex-col pb-12">
    @include('patient.partials.top-nav', ['active' => 'share'])

    <main class="flex-1 max-w-2xl mx-auto px-4 py-10 w-full space-y-6">
        <div>
            <h1 class="text-2xl font-black text-[#4A3F35] mb-2">رابط مشاهدة للطبيب</h1>
            <p class="text-sm text-[#6D5F52] font-medium leading-relaxed">
                أنت من يقرر متى يطلع الطبيب على ملخص ملفك. انسخ الرابط أدناه وأرسله للطبيب عبر قناة آمنة (واتساب خاص، عيادة، …).
                الرابط <strong>موقّع ومحدود الزمن</strong> (حوالي {{ $expiryMinutes }} دقيقة من لحظة فتح هذه الصفحة لكل رابط جديد).
            </p>
        </div>

        <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 font-semibold">
            لا تنشر الرابط في مجموعات عامة. أي شخص يملك الرابط خلال فترة الصلاحية قد يطلع على الملخص المعروض للطبيب.
        </div>

        <div class="bg-white border border-[#EAE3D2] rounded-3xl p-6 shadow-sm space-y-4">
            <label class="block text-sm font-black text-[#4A3F35]">رابط الملف للمشاهدة</label>
            <div class="space-y-2">
                <input id="share-profile-url" type="text" readonly value="{{ $profileUrl }}" dir="ltr" class="w-full font-mono text-xs sm:text-sm rounded-xl border border-[#EAE3D2] px-3 py-3 bg-[#FDFBF7] text-left">
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="copy-profile-url" class="flex-1 min-w-[7rem] bg-[#8D7456] text-white font-black px-4 py-3 rounded-xl hover:bg-[#725D45] transition-colors">
                        نسخ
                    </button>
                    <a
                        id="copy-open-profile-url"
                        href="{{ $profileUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        role="button"
                        class="flex-1 min-w-[7rem] inline-flex items-center justify-center bg-[#6a5a47] text-white font-black px-4 py-3 rounded-xl hover:bg-[#554837] transition-colors ring-2 ring-[#8D7456]/30 text-center no-underline"
                    >
                        نسخ وفتح
                    </a>
                </div>
            </div>

            <div class="pt-4 border-t border-[#F5EFE6] space-y-3">
                <p class="text-sm font-bold text-[#4A3F35]">تحميل PDF للطبيب (نفس صلاحية الرابط)</p>
                <div class="space-y-2">
                    <input id="share-pdf-url" type="text" readonly value="{{ $pdfUrl }}" dir="ltr" class="w-full font-mono text-xs sm:text-sm rounded-xl border border-[#EAE3D2] px-3 py-3 bg-[#FDFBF7] text-left">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="copy-pdf-url" class="flex-1 min-w-[7rem] border-2 border-[#8D7456] text-[#8D7456] font-black px-4 py-3 rounded-xl hover:bg-[#F5EFE6] transition-colors">
                            نسخ رابط PDF
                        </button>
                        <a
                            id="copy-open-pdf-url"
                            href="{{ $pdfUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            role="button"
                            class="flex-1 min-w-[7rem] inline-flex items-center justify-center border-2 border-[#6a5a47] text-[#6a5a47] font-black px-4 py-3 rounded-xl hover:bg-[#efe9df] transition-colors text-center no-underline"
                        >
                            نسخ وفتح PDF
                        </a>
                    </div>
                </div>
            </div>

            <a href="{{ \App\Support\PatientTemporaryProfileLink::whatsappShareUrl($profileUrl) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto bg-green-600 text-white font-black px-6 py-3 rounded-xl hover:bg-green-700 transition-colors">
                مشاركة عبر واتساب
            </a>
        </div>

        <p class="text-xs text-slate-400 text-center">إذا انتهت صلاحية الرابط، ارجع لهذه الصفحة لتوليد روابط جديدة.</p>
    </main>

    <script>
        function flashLabel(el, ok, doneMsg, failMsg) {
            var t = el.textContent;
            el.textContent = ok ? doneMsg : (failMsg || 'انسخ يدوياً');
            setTimeout(function () { el.textContent = t; }, 2200);
        }

        function copyFromInput(input) {
            var text = input.value;
            input.focus();
            input.select();
            input.setSelectionRange(0, 99999);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text).then(function () { return true; }).catch(function () { return legacyCopy(text); });
            }
            return Promise.resolve(legacyCopy(text));
        }

        function legacyCopy(text) {
            try {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                var ok = document.execCommand('copy');
                document.body.removeChild(ta);
                return ok;
            } catch (e) {
                return false;
            }
        }

        function setupCopy(buttonId, inputId) {
            var btn = document.getElementById(buttonId);
            var input = document.getElementById(inputId);
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                copyFromInput(input).then(function (ok) {
                    flashLabel(btn, ok, 'تم النسخ');
                }).catch(function () {
                    flashLabel(btn, false);
                });
            });
        }

        // رابط a حقيقي مع target=_blank: المتصفح لا يحجب الفتح كما قد يحجب window.open.
        function setupCopyOnAnchorOpen(anchorId, inputId) {
            var link = document.getElementById(anchorId);
            var input = document.getElementById(inputId);
            if (!link || !input) return;
            link.addEventListener('click', function () {
                copyFromInput(input).then(function (ok) {
                    flashLabel(link, ok, 'تم النسخ والفتح');
                }).catch(function () {
                    flashLabel(link, false, '', 'تم الفتح — انسخ يدوياً');
                });
            });
        }

        setupCopy('copy-profile-url', 'share-profile-url');
        setupCopyOnAnchorOpen('copy-open-profile-url', 'share-profile-url');
        setupCopy('copy-pdf-url', 'share-pdf-url');
        setupCopyOnAnchorOpen('copy-open-pdf-url', 'share-pdf-url');
    </script>
</body>
</html>
