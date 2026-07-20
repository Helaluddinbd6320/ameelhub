<?php

namespace App\Livewire\Public;

use App\Models\AgentProfile;
use App\Models\JobDeal;
use App\Services\SeoService;
use Livewire\Component;
use Livewire\WithPagination;

class AgentLeaderboard extends Component
{
    use WithPagination;

    public function render(SeoService $seoService)
    {
        view()->share('seo', $seoService->agentLeaderboard());

        $agents = AgentProfile::query()
            ->where('is_verified', true)
            ->withCount(['dealsAsAgent as successful_deals_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->addSelect([
                'workers_placed_count' => JobDeal::selectRaw('COUNT(DISTINCT worker_id)')
                    ->whereColumn('agent_id', 'agent_profiles.user_id')
                    ->where('status', 'completed'),
            ])
            ->orderByDesc('successful_deals_count')
            ->orderByDesc('workers_placed_count')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.public.agent-leaderboard', [
            'agents' => $agents,
        ])->layout('layouts.app');
    }
}