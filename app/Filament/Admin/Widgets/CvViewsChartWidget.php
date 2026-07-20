<?php

namespace App\Filament\Admin\Widgets;

use App\Models\CvView;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CvViewsChartWidget extends ChartWidget
{
    protected ?string $heading = 'CV Views (গত ৬ মাস)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $counts = $months->map(function (Carbon $month) {
            return CvView::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'CV Views',
                    'data' => $counts->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}