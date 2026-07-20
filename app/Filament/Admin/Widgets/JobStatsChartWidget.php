<?php

namespace App\Filament\Admin\Widgets;

use App\Models\JobPost;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class JobStatsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Job Post Stats (গত ৬ মাস)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $posted = $months->map(fn (Carbon $m) => JobPost::whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)->count());

        $approved = $months->map(fn (Carbon $m) => JobPost::whereYear('approved_at', $m->year)
            ->whereMonth('approved_at', $m->month)->count());

        $filled = $months->map(fn (Carbon $m) => JobPost::where('status', 'filled')
            ->whereYear('updated_at', $m->year)
            ->whereMonth('updated_at', $m->month)->count());

        return [
            'datasets' => [
                ['label' => 'Posted', 'data' => $posted->toArray(), 'backgroundColor' => '#3b82f6'],
                ['label' => 'Approved', 'data' => $approved->toArray(), 'backgroundColor' => '#22c55e'],
                ['label' => 'Filled', 'data' => $filled->toArray(), 'backgroundColor' => '#a855f7'],
            ],
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}