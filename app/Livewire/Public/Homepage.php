<?php

namespace App\Livewire\Public;

use App\Models\JobPost;
use App\Models\Worker;
use App\Services\SeoService;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Homepage extends Component
{
    public function render(SeoService $seoService)
    {
        view()->share('seo', $seoService->home());

        $today = Carbon::today();

        // ── Featured CVs: is_featured = true এবং featured_until এখনো valid ──
        // Step 10.8b Fix: ->with('skillCategory') — N+1 এড়াতে
        $featuredWorkers = Worker::query()
            ->with('skillCategory')
            ->whereIn('status', ['active', 'featured'])
            ->where('is_featured', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('featured_until')
                    ->orWhere('featured_until', '>=', $today);
            })
            ->inRandomOrder()
            ->limit(6)
            ->get();

        // ── বাকি Active CV (Featured গুলো বাদ দিয়ে), random ──
        $latestWorkers = Worker::query()
            ->with('skillCategory')
            ->whereIn('status', ['active', 'featured'])
            ->whereNotIn('id', $featuredWorkers->pluck('id'))
            ->inRandomOrder()
            ->limit(8)
            ->get();

        // ── Active Job Posts: expired না, vacancy বাকি আছে, random ──
        $latestJobs = JobPost::query()
            ->with('skillCategory')
            ->where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $today);
            })
            ->whereColumn('filled_count', '<', 'vacancies')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return view('livewire.public.homepage', [
            'featuredWorkers' => $featuredWorkers,
            'latestWorkers'   => $latestWorkers,
            'latestJobs'      => $latestJobs,
        ]);
    }
}