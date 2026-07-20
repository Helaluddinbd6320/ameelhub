<?php
// app/Livewire/Public/AgentProfilePage.php

namespace App\Livewire\Public;

use App\Models\AgentProfile;
use App\Models\JobPost;
use App\Services\SeoService;
use Illuminate\Support\Collection;
use Livewire\Component;

class AgentProfilePage extends Component
{
    public AgentProfile $agentProfile;

    public Collection $activeJobPosts;

    public function mount(AgentProfile $agentProfile, SeoService $seoService): void
    {
        // draft/unverified agent profile সরাসরি URL দিয়ে ঢুকতে চাইলে 404
        abort_if(
            $agentProfile->agent_name_bn === null && $agentProfile->agent_name_en === null,
            404
        );

        $this->agentProfile = $agentProfile->load('user');

        view()->share('seo', $seoService->agent($this->agentProfile));

        $this->activeJobPosts = JobPost::query()
            ->where('posted_by_id', $agentProfile->user_id)
            ->where('status', 'active')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.public.agent-profile-page')
            ->layout('layouts.app');
    }
}