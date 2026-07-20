<?php

namespace App\Filament\Agent\Widgets;

use App\Models\AgentNok;
use App\Models\JobDeal;
use App\Models\JobInterest;
use App\Models\JobPost;
use App\Models\Worker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AgentAnalyticsStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $agentId = Auth::id();

        // ১. এই এজেন্টের জমা দেওয়া CV গুলোর মোট view
        $cvViews = Worker::where('submitted_by_id', $agentId)->sum('view_count');

        // ২. Nok Response Rate — পাঠানো Nok এর মধ্যে কতগুলোর জবাব এসেছে (accept/reject)
        $totalNoksSent = AgentNok::where('agent_id', $agentId)->count();
        $respondedNoks = AgentNok::where('agent_id', $agentId)
            ->whereIn('status', ['accepted', 'rejected'])
            ->count();
        $responseRate = $totalNoksSent > 0
            ? round(($respondedNoks / $totalNoksSent) * 100, 1)
            : 0;

        // ৩. এই এজেন্টের Job Post গুলোতে আসা মোট Interest
        $agentJobPostIds = JobPost::where('posted_by_id', $agentId)->pluck('id');
        $interestCount = JobInterest::whereIn('job_post_id', $agentJobPostIds)->count();

        // ৪. মোট Deal Value (agent_fee_sar) — সব status মিলিয়ে
        $totalDealValue = JobDeal::where('agent_id', $agentId)->sum('agent_fee_sar');

        return [
            Stat::make('মোট CV Views', number_format($cvViews))
                ->description('আপনার জমা দেওয়া CV গুলো মিলিয়ে')
                ->color('info'),

            Stat::make('Nok Response Rate', $responseRate . '%')
                ->description($respondedNoks . ' / ' . $totalNoksSent . ' Nok-এ সাড়া পেয়েছেন')
                ->color($responseRate >= 50 ? 'success' : 'warning'),

            Stat::make('মোট Interest পেয়েছেন', number_format($interestCount))
                ->description('আপনার Job Post গুলোতে')
                ->color('primary'),

            Stat::make('মোট Deal Value', number_format($totalDealValue, 2) . ' SAR')
                ->description('সব Deal মিলিয়ে (fee অনুযায়ী)')
                ->color('success'),
        ];
    }
}