<?php

namespace App\Livewire\Jobs;

use App\Models\JobInterest;
use App\Models\JobPost;
use App\Services\JobInterestService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SubmitInterest extends Component
{
    public JobPost $jobPost;

    #[Validate('nullable|string|max:500')]
    public string $note = '';

    public bool $showNoteForm = false;
    public bool $alreadyApplied = false;
    public ?string $errorMessage = null;

    public function mount(JobPost $jobPost): void
    {
        $this->jobPost = $jobPost;

        if (auth()->check() && auth()->user()->role === 'worker') {
            $this->alreadyApplied = JobInterest::where('job_post_id', $jobPost->id)
                ->whereHas('worker', fn ($q) => $q->where('worker_user_id', auth()->id()))
                ->exists();
        }
    }

    public function openNoteForm(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if (auth()->user()->role !== 'worker') {
            $this->errorMessage = 'শুধুমাত্র Worker অ্যাকাউন্ট থেকে আবেদন করা যাবে।';
            return;
        }

        $this->errorMessage = null;
        $this->showNoteForm = true;
    }

    public function submit(JobInterestService $service): void
    {
        $this->validate();
        $this->errorMessage = null;

        try {
            $service->submitWorkerInterest(auth()->user(), $this->jobPost, $this->note ?: null);

            $this->alreadyApplied = true;
            $this->showNoteForm = false;
            $this->dispatch('interest-submitted');
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        }
    }

    public function render()
    {
        return view('livewire.jobs.submit-interest');
    }
}