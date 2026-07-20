<?php

namespace App\Filament\Admin\Widgets;

use App\Models\JobDeal;
use App\Models\JobDealMilestone;
use App\Models\JobPost;
use App\Models\Worker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalCvViews = Worker::sum('view_count');
        $activeJobs = JobPost::where('status', 'active')->count();
        $completedDeals = JobDeal::where('status', 'completed')->count();

        $monthlyRevenue = JobDealMilestone::where('status', 'admin_released')
            ->whereYear('admin_released_at', now()->year)
            ->whereMonth('admin_released_at', now()->month)
            ->sum('commission_sar');

        return [
            Stat::make('মোট CV Views', number_format($totalCvViews))
                ->description('সব CV মিলিয়ে')
                ->color('info'),

            Stat::make('Active Job Posts', number_format($activeJobs))
                ->description('বর্তমানে visible')
                ->color('success'),

            Stat::make('Completed Deals', number_format($completedDeals))
                ->description('সম্পূর্ণ সফল ডিল')
                ->color('success'),

            Stat::make('এই মাসের Revenue', number_format($monthlyRevenue, 2) . ' SAR')
                ->description('Chapai Commission')
                ->color('warning'),
        ];
    }
}