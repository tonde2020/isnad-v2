<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Patient;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -20;

    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return auth()->user()?->role === UserRole::Admin;
    }

    /**
     * لوحة المشرف التشغيلي: أعداد فقط — دون أي بيانات تعريفية أو سريرية على مستوى الأفراد.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $patientTotal = Patient::query()->count();

        $sparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $sparkline[] = Patient::query()->whereDate('created_at', $day)->count();
        }

        return [
            Stat::make('إجمالي ملفات المرضى', (string) $patientTotal)
                ->description('عدد السجلات فقط — التفاصيل متاحة للمريض ولطاقم السجلات الطبية')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('primary')
                ->icon(Heroicon::OutlinedUserGroup)
                ->chart($sparkline),

            Stat::make('أرقام السجل', 'من قائمة المرضى')
                ->description('يُعرض للمشرف رقم السجل وتاريخ الإنشاء فقط عند فتح أي ملف')
                ->descriptionIcon(Heroicon::OutlinedIdentification)
                ->color('gray')
                ->icon(Heroicon::OutlinedShieldCheck),
        ];
    }
}
