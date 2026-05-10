<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Patient;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRegistrationsChart extends ChartWidget
{
    protected static ?int $sort = -10;

    protected static bool $isLazy = false;

    protected ?string $heading = 'تسجيل المرضى شهرياً';

    protected ?string $description = 'آخر ستة أشهر — إجمالي ملفات المرضى المُنشأة في كل شهر.';

    protected string $color = 'primary';

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->role === UserRole::Admin;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $labels[] = $month->locale('ar')->translatedFormat('M Y');

            $values[] = Patient::query()
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'ملفات جديدة',
                    'data' => $values,
                    'backgroundColor' => '#fcfaf2',
                    'borderColor' => '#d4af37',
                    'borderWidth' => 1,
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
