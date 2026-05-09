<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class MonthlyRegistrationsChart extends ChartWidget
{
    protected static ?int $sort = -10;

    protected static bool $isLazy = false;

    protected ?string $heading = 'إحصائيات التسجيل الشهري';

    protected ?string $description = 'جارت Placeholder جاهز لربطه بالبيانات لاحقاً.';

    protected string $color = 'primary';

    protected ?string $maxHeight = '280px';

    protected int | string | array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'التسجيلات',
                    'data' => [0, 0, 0, 0, 0, 0],
                    'backgroundColor' => '#fcfaf2',
                    'borderColor' => '#d4af37',
                    'borderWidth' => 1,
                    'borderRadius' => 8,
                ],
            ],
            'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
        ];
    }
}
