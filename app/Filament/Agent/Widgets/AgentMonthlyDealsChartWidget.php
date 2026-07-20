<?php

namespace App\Filament\Agent\Widgets;

use App\Models\JobDeal;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AgentMonthlyDealsChartWidget extends ChartWidget
{
    protected ?string $heading = 'মাসিক Deal সংখ্যা (গত ৬ মাস)';

    protected function getData(): array
    {
        $agentId = Auth::id();
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $deals = $months->map(function (Carbon $month) use ($agentId) {
            return JobDeal::where('agent_id', $agentId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        });

        $completed = $months->map(function (Carbon $month) use ($agentId) {
            return JobDeal::where('agent_id', $agentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $month->year)
                ->whereMonth('completed_at', $month->month)
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'মোট Deal',
                    'data' => $deals->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Completed',
                    'data' => $completed->toArray(),
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}