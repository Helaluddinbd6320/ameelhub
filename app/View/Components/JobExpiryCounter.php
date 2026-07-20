<?php

namespace App\View\Components;

use App\Models\JobPost;
use Carbon\Carbon;
use Illuminate\View\Component;
use Illuminate\View\View;

class JobExpiryCounter extends Component
{
    public ?Carbon $expiresAt;

    public ?int $daysLeft;

    /** expired | urgent | warning | safe | none */
    public string $urgency;

    public string $label;

    public function __construct(?JobPost $job = null, ?string $expiresAt = null)
    {
        $raw = $job?->expires_at ?? $expiresAt;

        $this->expiresAt = $raw ? Carbon::parse($raw) : null;

        if (is_null($this->expiresAt)) {
            $this->urgency = 'none';
            $this->daysLeft = null;
            $this->label = 'মেয়াদ নির্ধারিত নয়';

            return;
        }

        $today = Carbon::now()->startOfDay();
        $expiry = $this->expiresAt->copy()->startOfDay();
        $this->daysLeft = (int) $today->diffInDays($expiry, false);

        if ($this->daysLeft < 0) {
            $this->urgency = 'expired';
            $this->label = 'মেয়াদ শেষ হয়ে গেছে';
        } elseif ($this->daysLeft === 0) {
            $this->urgency = 'urgent';
            $this->label = 'আজই শেষ দিন';
        } elseif ($this->daysLeft <= 7) {
            $this->urgency = 'urgent';
            $this->label = "মাত্র {$this->daysLeft} দিন বাকি";
        } elseif ($this->daysLeft <= 30) {
            $this->urgency = 'warning';
            $this->label = "{$this->daysLeft} দিন বাকি";
        } else {
            $this->urgency = 'safe';
            $this->label = "{$this->daysLeft} দিন বাকি";
        }
    }

    public function render(): View
    {
        return view('components.job-expiry-counter');
    }
}