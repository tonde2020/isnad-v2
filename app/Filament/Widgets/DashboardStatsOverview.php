<?php

namespace App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -20;

    protected static bool $isLazy = false;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي الطلاب', '--')
                ->description('قيد الربط بالبيانات')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('primary')
                ->icon(Heroicon::AcademicCap)
                ->chart([0, 0, 0, 0, 0, 0]),

            Stat::make('الدروس المكتملة', '--')
                ->description('قيد الربط بالبيانات')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('success')
                ->icon(Heroicon::CheckBadge)
                ->chart([0, 0, 0, 0, 0, 0]),

            Stat::make('الدورات النشطة', '--')
                ->description('قيد الربط بالبيانات')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning')
                ->icon(Heroicon::BookOpen)
                ->chart([0, 0, 0, 0, 0, 0]),

            Stat::make('التسجيلات الشهرية', '--')
                ->description('قيد الربط بالبيانات')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('info')
                ->icon(Heroicon::ChartBar)
                ->chart([0, 0, 0, 0, 0, 0]),
        ];
    }
}
