<?php

namespace App\Livewire\Public;

use App\Models\SkillCategory;
use App\Models\Worker;
use App\Services\SeoService;
use Livewire\Component;
use Livewire\WithPagination;

class WorkerList extends Component
{
    use WithPagination;

    public ?int $skillCategoryId = null;

    public ?string $visaStatus = null;

    public ?bool $isInSaudi = null;

    public int $seed;

    protected $queryString = [
        'skillCategoryId' => ['except' => null],
        'visaStatus'      => ['except' => null],
        'isInSaudi'       => ['except' => null],
        'seed'            => ['except' => ''],
    ];

    public function mount(SeoService $seoService): void
    {
        view()->share('seo', $seoService->workerList());

        $this->seed = (int) request('seed', session('browse_seed', rand(1, 9999)));
        session(['browse_seed' => $this->seed]);
    }

    public function updatingSkillCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingVisaStatus(): void
    {
        $this->resetPage();
    }

    public function updatingIsInSaudi(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['skillCategoryId', 'visaStatus', 'isInSaudi']);
        $this->resetPage();
    }

    public function render()
    {
        $today = now()->toDateString();

        // ১. কর্মীদের লিস্ট কোয়েরি (ফিল্টারসহ)
        // Step 10.8b Fix: ->with('skillCategory') — কার্ড ভিউতে পেশা দেখানোর সময় N+1 এড়াতে
        $workers = Worker::query()
            ->with('skillCategory')
            ->whereIn('status', ['active', 'featured'])
            ->when($this->skillCategoryId, fn ($q) => $q->where('skill_category_id', $this->skillCategoryId))
            ->when($this->visaStatus, fn ($q) => $q->where('visa_status', $this->visaStatus))
            ->when(! is_null($this->isInSaudi), fn ($q) => $q->where('is_in_saudi', $this->isInSaudi))
            ->orderByRaw(
                'CASE WHEN is_featured = 1 AND (featured_until IS NULL OR featured_until >= ?) THEN 0 ELSE 1 END',
                [$today]
            )
            ->inRandomOrder($this->seed)
            ->paginate(12);

        // ২. ড্রপডাউনের জন্য পেশা বা ক্যাটাগরি কোয়েরি (অ্যাক্টিভ কর্মীদের কাউন্টসহ)
        $skillCategories = SkillCategory::where('is_active', true)
            ->withCount(['workers' => function ($query) {
                $query->whereIn('status', ['active', 'featured']);
            }])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.public.worker-list', [
            'workers'         => $workers,
            'skillCategories' => $skillCategories,
        ]);
    }
}