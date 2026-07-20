<?php

namespace App\Livewire;

use App\Models\JobPost;
use App\Models\SkillCategory;
use App\Services\SeoService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class JobList extends Component
{
    use WithPagination;

    #[Url(as: 'skill')]
    public ?int $skillCategoryId = null;

    #[Url(as: 'city')]
    public ?string $employerCity = null;

    #[Url(as: 'min_salary')]
    public ?float $minSalary = null;

    #[Url(as: 'max_salary')]
    public ?float $maxSalary = null;

    #[Url(as: 'accommodation')]
    public bool $accommodationOnly = false;

    #[Url(as: 'food')]
    public bool $foodOnly = false;

    #[Url(as: 'transport')]
    public bool $transportOnly = false;

    #[Url(as: 'seed')]
    public ?int $seed = null;

    public function mount(SeoService $seoService): void
    {
        view()->share('seo', $seoService->jobList());

        // Blueprint Section 11: seeded random order, persisted per browse session
        if (! $this->seed) {
            $this->seed = session('job_browse_seed', random_int(1, 9999));
        }

        session(['job_browse_seed' => $this->seed]);
    }

    public function updatingSkillCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingEmployerCity(): void
    {
        $this->resetPage();
    }

    public function updatingMinSalary(): void
    {
        $this->resetPage();
    }

    public function updatingMaxSalary(): void
    {
        $this->resetPage();
    }

    public function updatingAccommodationOnly(): void
    {
        $this->resetPage();
    }

    public function updatingFoodOnly(): void
    {
        $this->resetPage();
    }

    public function updatingTransportOnly(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'skillCategoryId',
            'employerCity',
            'minSalary',
            'maxSalary',
            'accommodationOnly',
            'foodOnly',
            'transportOnly',
        ]);

        $this->resetPage();
    }

    /**
     * "রিফ্রেশ / নতুন করে দেখুন" বাটন — নতুন random seed জেনারেট করে
     */
    public function reshuffle(): void
    {
        $this->seed = random_int(1, 9999);
        session(['job_browse_seed' => $this->seed]);
        $this->resetPage();
    }

    public function render()
    {
        $jobs = JobPost::query()
            ->where('status', 'active')
            ->whereColumn('filled_count', '<', 'vacancies')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->when($this->skillCategoryId, fn ($q) => $q->where('skill_category_id', $this->skillCategoryId))
            ->when($this->employerCity, fn ($q) => $q->where('employer_city', 'like', '%'.$this->employerCity.'%'))
            ->when($this->minSalary, fn ($q) => $q->where('salary_sar', '>=', $this->minSalary))
            ->when($this->maxSalary, fn ($q) => $q->where('salary_sar', '<=', $this->maxSalary))
            ->when($this->accommodationOnly, fn ($q) => $q->where('accommodation', true))
            ->when($this->foodOnly, fn ($q) => $q->where('food_included', true))
            ->when($this->transportOnly, fn ($q) => $q->where('transport_provided', true))
            ->with('skillCategory')
            ->orderByRaw('RAND('.(int) $this->seed.')')
            ->paginate(12);

        $skillCategories = SkillCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.job-list', [
            'jobs' => $jobs,
            'skillCategories' => $skillCategories,
        ])->layout('layouts.app');
    }
}