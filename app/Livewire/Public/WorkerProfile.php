<?php

namespace App\Livewire\Public;

use App\Models\AgentNok;
use App\Models\ContactReveal;
use App\Models\CvView;
use App\Models\JobPost;
use App\Models\Worker;
use App\Services\ContactRevealService;
use App\Services\NokService;
use App\Services\SeoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class WorkerProfile extends Component
{
    public Worker $worker;

    public array $revealedPhones = [];

    // --- Route B: Agent Nok (Job Offer) state ---
    public bool $showNokModal = false;
    public ?int $selectedJobPostId = null;
    public string $nokMessage = '';
    public ?string $nokError = null;
    public ?string $nokSuccess = null;

    public function mount(Worker $worker, SeoService $seoService): void
    {
        abort_unless(in_array($worker->status, ['active', 'featured'], true), 404);

        $this->worker = $worker;

        view()->share('seo', $seoService->worker($worker));

        $this->trackView();

        if (Auth::check()) {
            $this->loadRevealedPhones();
        }
    }

    protected function trackView(): void
    {
        $sessionKey = 'viewed_worker_' . $this->worker->id;

        if (! session()->has($sessionKey)) {
            session()->put($sessionKey, true);

            CvView::create([
                'worker_id'  => $this->worker->id,
                'ip_address' => request()->ip(),
            ]);

            // Atomic increment — bypasses $guarded (not mass assignment), race-condition safe
            $this->worker->increment('view_count');
        }
    }

    protected function loadRevealedPhones(): void
    {
        $this->revealedPhones = ContactReveal::where('user_id', Auth::id())
            ->where('worker_id', $this->worker->id)
            ->pluck('phone_type')
            ->toArray();
    }

    public function revealPhone(string $phoneType, ContactRevealService $service): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');
            return;
        }

        try {
            $service->reveal($this->worker, $phoneType, Auth::user());
            $this->loadRevealedPhones();
        } catch (ValidationException $e) {
            session()->flash('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            session()->flash('error', 'পর্যাপ্ত ব্যালেন্স নেই। Wallet রিচার্জ করুন।');
        }
    }

    public function getYoutubeEmbedUrlProperty(): ?string
    {
        $url = $this->worker->skill_video_youtube;

        if (! $url) {
            return null;
        }

        preg_match(
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
            $url,
            $matches
        );

        return isset($matches[1]) ? "https://www.youtube.com/embed/{$matches[1]}" : null;
    }

    public function getIqamaStatusProperty(): ?array
    {
        if (! $this->worker->iqama_expiry) {
            return null;
        }

        $daysLeft = (int) now()->diffInDays($this->worker->iqama_expiry, false);

        return match (true) {
            $daysLeft < 0   => ['label' => 'মেয়াদ শেষ', 'badgeClass' => 'bg-red-50 text-red-700'],
            $daysLeft <= 30 => ['label' => "মাত্র {$daysLeft} দিন বাকি", 'badgeClass' => 'bg-red-50 text-red-700'],
            $daysLeft <= 90 => ['label' => "{$daysLeft} দিন বাকি", 'badgeClass' => 'bg-yellow-50 text-yellow-700'],
            default         => ['label' => "{$daysLeft} দিন বাকি", 'badgeClass' => 'bg-green-50 text-green-700'],
        };
    }

    // --- Route B: Agent Nok ---

    public function getIsAgentProperty(): bool
    {
        return Auth::check() && Auth::user()->role === 'agent';
    }

    /**
     * এই Agent এর নিজের Active job posts, যেগুলোর vacancy এখনো খালি আছে।
     * প্রতিটার সাথে এই Worker কে আগে Nok পাঠানো হয়েছে কিনা তার status।
     */
    public function getAgentActiveJobPostsProperty()
    {
        if (! $this->isAgent) {
            return collect();
        }

        $jobPosts = JobPost::where('posted_by_id', Auth::id())
            ->where('status', 'active')
            ->whereColumn('filled_count', '<', 'vacancies')
            ->orderByDesc('created_at')
            ->get(['id', 'job_title', 'employer_city', 'salary_sar']);

        $nokStatuses = AgentNok::where('agent_id', Auth::id())
            ->where('worker_id', $this->worker->id)
            ->whereIn('job_post_id', $jobPosts->pluck('id'))
            ->pluck('status', 'job_post_id');

        return $jobPosts->map(function ($job) use ($nokStatuses) {
            $job->nok_status = $nokStatuses->get($job->id);
            return $job;
        });
    }

    public function openNokModal(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login');
            return;
        }

        if (! $this->isAgent) {
            $this->nokError = 'শুধুমাত্র Agent অ্যাকাউন্ট থেকে Job Offer পাঠানো যাবে।';
            return;
        }

        $this->nokError = null;
        $this->nokSuccess = null;
        $this->selectedJobPostId = null;
        $this->nokMessage = '';
        $this->showNokModal = true;
    }

    public function closeNokModal(): void
    {
        $this->showNokModal = false;
        $this->nokError = null;
    }

    public function sendNok(NokService $service): void
    {
        $this->nokError = null;
        $this->nokSuccess = null;

        if (! $this->isAgent) {
            $this->nokError = 'শুধুমাত্র Agent অ্যাকাউন্ট থেকে Job Offer পাঠানো যাবে।';
            return;
        }

        if (! $this->selectedJobPostId) {
            $this->nokError = 'একটি Job Post নির্বাচন করুন।';
            return;
        }

        try {
            $service->send(
                jobPostId: $this->selectedJobPostId,
                workerId: $this->worker->id,
                message: $this->nokMessage ?: null,
                route: 'route_b',
            );

            $this->nokSuccess = 'Job Offer সফলভাবে পাঠানো হয়েছে।';
            $this->showNokModal = false;

            // refresh nok_status badge on the (currently closed) modal list
            unset($this->agentActiveJobPosts);
        } catch (ValidationException $e) {
            $this->nokError = collect($e->errors())->flatten()->first();
        }
    }

    public function render()
    {
        // ক্যাটাগরি আইডি এবং বর্তমান ওয়ার্কার আইডি আলাদা করে কুয়েরি অপ্টিমাইজ করা হয়েছে
        $categoryId = $this->worker->skill_category_id;
        $currentWorkerId = $this->worker->id;

        // মডেলে থাকা 'skillCategory' ম্যাজিক মেথড ইগার লোড করা হলো
        $similarWorkers = Worker::query()
            ->where('skill_category_id', $categoryId)
            ->where('id', '!=', $currentWorkerId)
            ->whereIn('status', ['active', 'featured'])
            ->with('skillCategory')
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.public.worker-profile', [
            'similarWorkers' => $similarWorkers
        ]);
    }
}