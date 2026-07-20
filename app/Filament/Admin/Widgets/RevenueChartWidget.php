<?php

namespace App\Filament\Admin\Widgets;

use App\Models\JobDealMilestone;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Monthly Revenue — Chapai Commission (SAR)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $revenue = $months->map(function (Carbon $m) {
            return JobDealMilestone::where('status', 'admin_released')
                ->whereYear('admin_released_at', $m->year)
                ->whereMonth('admin_released_at', $m->month)
                ->sum('commission_sar');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (SAR)',
                    'data' => $revenue->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
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