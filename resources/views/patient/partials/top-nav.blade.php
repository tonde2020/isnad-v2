@php
    $active = $active ?? 'home';
@endphp
<header class="bg-[#8D7456] text-white shadow-lg">
    <div class="max-w-3xl mx-auto px-4 py-4 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ url('/app') }}" class="font-black text-lg hover:opacity-90">إسناد — لوحتي</a>
            @if($active === 'home')
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wide bg-white/20 px-2 py-1 rounded-lg">أنت هنا</span>
            @endif
        </div>
        <nav class="flex flex-wrap items-center gap-2">
            <a href="{{ url('/app') }}" @class([
                'text-sm font-bold px-4 py-2 rounded-xl transition-colors',
                'bg-white text-[#8D7456] shadow-sm' => $active === 'home',
                'bg-white/15 hover:bg-white/25 text-white' => $active !== 'home',
            ])>الرئيسية</a>
            <a href="{{ route('patient.profile.edit') }}" @class([
                'text-sm font-bold px-4 py-2 rounded-xl transition-colors',
                'bg-white text-[#8D7456] shadow-sm' => $active === 'profile',
                'bg-white/15 hover:bg-white/25 text-white' => $active !== 'profile',
            ])>بياناتي</a>
            <a href="{{ route('patient.share') }}" @class([
                'text-sm font-bold px-4 py-2 rounded-xl transition-colors',
                'bg-white text-[#8D7456] shadow-sm' => $active === 'share',
                'bg-white/15 hover:bg-white/25 text-white' => $active !== 'share',
            ])>رابط للطبيب</a>
            <a href="{{ url('/') }}" class="text-sm font-bold bg-white/15 hover:bg-white/25 px-4 py-2 rounded-xl transition-colors">الموقع العام</a>
            <form method="post" action="{{ route('patient.logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm font-bold bg-white/15 hover:bg-white/25 px-4 py-2 rounded-xl transition-colors">خروج</button>
            </form>
        </nav>
    </div>
</header>
