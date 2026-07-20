<?php

namespace App\View\Components;

use App\Models\JobPost;
use Illuminate\View\Component;
use Illuminate\View\View;

class JobVacancyCounter extends Component
{
    public int $vacancies;

    public int $filledCount;

    public int $remaining;

    public float $percentFilled;

    /** full | urgent | warning | safe */
    public string $urgency;

    public string $label;

    public function __construct(?JobPost $job = null, ?int $vacancies = null, ?int $filledCount = null)
    {
        $this->vacancies = $job->vacancies ?? $vacancies ?? 0;
        $this->filledCount = $job->filled_count ?? $filledCount ?? 0;
        $this->remaining = max(0, $this->vacancies - $this->filledCount);

        $this->percentFilled = $this->vacancies > 0
            ? round(($this->filledCount / $this->vacancies) * 100, 1)
            : 100.0;

        if ($this->remaining <= 0) {
            $this->urgency = 'full';
            $this->label = 'সব পদ পূর্ণ হয়ে গেছে';
        } elseif ($this->remaining === 1) {
            $this->urgency = 'urgent';
            $this->label = 'মাত্র ১ জন লাগবে';
        } elseif ($this->vacancies > 0 && ($this->remaining / $this->vacancies) <= 0.3) {
            $this->urgency = 'warning';
            $this->label = "{$this->remaining} জন বাকি";
        } else {
            $this->urgency = 'safe';
            $this->label = "{$this->remaining} জন খালি আছে";
        }
    }

    public function render(): View
    {
        return view('components.job-vacancy-counter');
    }
}