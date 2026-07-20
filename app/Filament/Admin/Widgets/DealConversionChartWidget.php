<?php

namespace App\Filament\Admin\Widgets;

use App\Models\JobDeal;
use App\Models\JobInterest;
use App\Models\JobSelection;
use Filament\Widgets\ChartWidget;

class DealConversionChartWidget extends ChartWidget
{
    protected ?string $heading = 'Deal Conversion Funnel (সর্বমোট)';

    protected function getData(): array
    {
        $interests = JobInterest::count();
        $selections = JobSelection::count();
        $accepted = JobSelection::where('worker_response', 'accepted')->count();
        $deals = JobDeal::count();
        $completed = JobDeal::where('status', 'completed')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Count',
                    'data' => [$interests, $selections, $accepted, $deals, $completed],
                    'backgroundColor' => ['#3b82f6', '#f59e0b', '#22c55e', '#8b5cf6', '#10b981'],
                ],
            ],
            'labels' => ['Interest', 'Selection', 'Worker Accepted', 'Deal Confirmed', 'Completed'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
        ];
    }
}