<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Iqra') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen bg-accent font-sans text-ink antialiased">
        <div class="min-h-screen">
            <header class="border-b border-border-soft bg-white/70 backdrop-blur-sm">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                    <a href="{{ url('/') }}" class="text-xl font-extrabold text-ink">
                        إقرأ
                    </a>

                    <nav class="flex items-center gap-4 text-sm font-bold text-gray-500">
                        <a href="{{ url('/component-guide') }}" class="transition hover:text-ink">دليل المكونات</a>
                        <a href="{{ url('/app') }}" class="transition hover:text-ink">لوحة التطبيق</a>
                    </nav>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-6 py-10">
                {{ $slot }}
            </main>

            <footer class="mx-auto max-w-7xl px-6 pb-8 text-center text-xs font-semibold text-gray-500">
                تم التطوير بواسطة
                <a href="https://github.com/mnasir" class="font-bold text-ink hover:text-primary">mnasir</a>
            </footer>
        </div>
    </body>
</html>
