<?php

namespace App\Filament\Admin\Resources\JobDeals\Pages;

use App\Models\DisputeEvidence;
use App\Filament\Admin\Resources\JobDeals\JobDealResource;
use App\Models\JobDealMilestone;
use App\Services\DisputeService;
use App\Services\MilestoneService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class ViewJobDeal extends ViewRecord
{
    protected static string $resource = JobDealResource::class;

    protected string $view = 'filament.admin.resources.job-deals.pages.view-job-deal';

    /** Release confirm modal এর জন্য নির্বাচিত মাইলস্টোন আইডি */
    public ?int $releasingMilestoneId = null;

    /** Dispute resolve modal এর জন্য state */
    public ?int $resolvingMilestoneId = null;
    public string $resolutionAction = 'full_refund'; // full_refund | full_release | partial | extend
    public ?float $partialWorkerPct = 50;
    public string $resolutionNotes = '';

    /** Agent profile quick-view modal */
    public bool $showAgentModal = false;

    /** Worker profile quick-view modal */
    public bool $showWorkerModal = false;

    public function getMilestonesProperty()
    {
        return $this->record->milestones()->with('evidences')->orderBy('milestone_number')->get();
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    // ─────────────────────────────────────────────
    // Agent Profile Quick View
    // ─────────────────────────────────────────────

    public function openAgentProfileModal(): void
    {
        if (! $this->record->agent?->agentProfile) {
            Notification::make()
                ->title('এজেন্টের প্রোফাইল তথ্য পাওয়া যায়নি')
                ->warning()
                ->send();
            return;
        }

        $this->showAgentModal = true;
    }

    public function closeAgentProfileModal(): void
    {
        $this->showAgentModal = false;
    }

    // ─────────────────────────────────────────────
    // Worker Profile Quick View
    // ─────────────────────────────────────────────

    public function openWorkerProfileModal(): void
    {
        if (! $this->record->worker) {
            Notification::make()
                ->title('ওয়ার্কারের প্রোফাইল তথ্য পাওয়া যায়নি')
                ->warning()
                ->send();
            return;
        }

        $this->showWorkerModal = true;
    }

    public function closeWorkerProfileModal(): void
    {
        $this->showWorkerModal = false;
    }

    // ─────────────────────────────────────────────
    // Release (Step 6.4 — অপরিবর্তিত)
    // ─────────────────────────────────────────────

    public function openReleaseModal(int $milestoneId): void
    {
        if (! $this->isSuperAdmin()) {
            Notification::make()
                ->title('আপনার এই কাজের অনুমতি নেই')
                ->body('শুধুমাত্র Super Admin মাইলস্টোন পেমেন্ট রিলিজ করতে পারবেন।')
                ->danger()
                ->send();
            return;
        }

        $this->releasingMilestoneId = $milestoneId;
    }

    public function closeReleaseModal(): void
    {
        $this->releasingMilestoneId = null;
    }

    public function releaseMilestone(MilestoneService $service): void
    {
        if (! $this->releasingMilestoneId || ! $this->isSuperAdmin()) {
            $this->releasingMilestoneId = null;
            return;
        }

        $milestone = JobDealMilestone::find($this->releasingMilestoneId);

        if (! $milestone) {
            $this->releasingMilestoneId = null;
            return;
        }

        try {
            $service->releaseByAdmin($milestone, auth()->user());

            Notification::make()
                ->title('পেমেন্ট রিলিজ করা হয়েছে')
                ->body('Agent এর ওয়ালেটে পেমেন্ট পাঠানো হয়েছে।')
                ->success()
                ->send();

            $this->record->refresh();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('রিলিজ করা যায়নি')
                ->body(collect($e->errors())->flatten()->first() ?? 'একটি সমস্যা হয়েছে।')
                ->danger()
                ->send();
        }

        $this->releasingMilestoneId = null;
    }

    // ─────────────────────────────────────────────
    // Dispute Resolution (Step 6.7 — নতুন)
    // ─────────────────────────────────────────────

    public function openDisputeModal(int $milestoneId): void
    {
        if (! $this->isSuperAdmin()) {
            Notification::make()
                ->title('আপনার এই কাজের অনুমতি নেই')
                ->body('শুধুমাত্র Super Admin বিরোধ সমাধান করতে পারবেন।')
                ->danger()
                ->send();
            return;
        }

        $milestone = JobDealMilestone::find($milestoneId);

        if (! $milestone || ! $milestone->isDisputed()) {
            Notification::make()
                ->title('এই মাইলস্টোনে কোনো চলমান বিরোধ নেই')
                ->warning()
                ->send();
            return;
        }

        $this->resolvingMilestoneId = $milestoneId;
        $this->resolutionAction     = 'full_refund';
        $this->partialWorkerPct     = 50;
        $this->resolutionNotes      = '';
    }

    public function closeDisputeModal(): void
    {
        $this->resolvingMilestoneId = null;
        $this->resolutionNotes      = '';
    }

    public function submitDisputeResolution(DisputeService $service): void
    {
        if (! $this->resolvingMilestoneId || ! $this->isSuperAdmin()) {
            $this->resolvingMilestoneId = null;
            return;
        }

        $milestone = JobDealMilestone::find($this->resolvingMilestoneId);

        if (! $milestone) {
            $this->resolvingMilestoneId = null;
            return;
        }

        if (trim($this->resolutionNotes) === '') {
            Notification::make()
                ->title('সমাধানের নোট আবশ্যক')
                ->body('অ্যাডমিন নোট ছাড়া বিরোধ সমাধান করা যাবে না।')
                ->danger()
                ->send();
            return;
        }

        try {
            if ($this->resolutionAction === 'extend') {
                // Dispute-এর আগে যে confirm অবস্থায় ছিলো, সেখানেই ফিরিয়ে দেওয়া হবে
                $revertTo = match (true) {
                    $milestone->agent_confirmed_at !== null  => 'agent_confirmed',
                    $milestone->worker_confirmed_at !== null => 'worker_confirmed',
                    default                                   => 'pending',
                };

                $service->extendDeadline($milestone, $this->resolutionNotes, auth()->user(), $revertTo);

                Notification::make()
                    ->title('সময়সীমা বর্ধিত করা হয়েছে')
                    ->body('মাইলস্টোনটি আবার কনফার্মেশনের জন্য খুলে দেওয়া হয়েছে।')
                    ->success()
                    ->send();
            } else {
                $service->resolve(
                    $milestone,
                    $this->resolutionAction,
                    $this->resolutionNotes,
                    auth()->user(),
                    $this->resolutionAction === 'partial' ? $this->partialWorkerPct : null
                );

                Notification::make()
                    ->title('বিরোধ সমাধান করা হয়েছে')
                    ->body('সংশ্লিষ্ট পক্ষদের ওয়ালেটে যথাযথ পরিমাণ সমন্বয় করা হয়েছে।')
                    ->success()
                    ->send();
            }

            $this->record->refresh();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('সমাধান করা যায়নি')
                ->body(collect($e->errors())->flatten()->first() ?? 'একটি সমস্যা হয়েছে।')
                ->danger()
                ->send();
        }

        $this->resolvingMilestoneId = null;
        $this->resolutionNotes      = '';
    }

    /** Evidence ফাইলের signed download URL — ৩০ মিনিট মেয়াদ */
    public function evidenceDownloadUrl(DisputeEvidence $evidence): string
    {
        return URL::temporarySignedRoute(
            'dispute-evidence.download',
            now()->addMinutes(30),
            ['evidence' => $evidence->id]
        );
    }

    // ─────────────────────────────────────────────
    // Labels
    // ─────────────────────────────────────────────

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'          => 'অপেক্ষমান',
            'worker_confirmed' => 'Worker কনফার্ম করেছে',
            'agent_confirmed'  => 'Agent কনফার্ম করেছে — রিলিজের অপেক্ষায়',
            'admin_released'   => 'পেমেন্ট রিলিজ হয়েছে',
            'disputed'         => 'বিরোধ চলমান',
            default            => $status,
        };
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'pending'          => 'gray',
            'worker_confirmed' => 'info',
            'agent_confirmed'  => 'warning',
            'admin_released'   => 'success',
            'disputed'         => 'danger',
            default            => 'gray',
        };
    }

    public static function disputeRaisedByLabel(?string $role): string
    {
        return match ($role) {
            'worker' => 'Worker',
            'agent'  => 'Agent',
            default  => '—',
        };
    }

    public static function resolutionLabel(?string $resolution): string
    {
        return match ($resolution) {
            'released' => 'সম্পূর্ণ রিলিজ করা হয়েছে',
            'refunded' => 'সম্পূর্ণ ফেরত দেওয়া হয়েছে',
            'partial'  => 'আংশিক সমাধান হয়েছে',
            default    => '—',
        };
    }

    public static function workerStatusLabel(string $status): string
    {
        return match ($status) {
            'draft'    => 'ড্রাফট',
            'pending'  => 'অনুমোদনের অপেক্ষায়',
            'active'   => 'সক্রিয়',
            'featured' => 'ফিচার্ড',
            'hired'    => 'নিয়োগপ্রাপ্ত',
            'inactive' => 'নিষ্ক্রিয়',
            'rejected' => 'প্রত্যাখ্যাত',
            default    => $status,
        };
    }

    public static function workerStatusColor(string $status): string
    {
        return match ($status) {
            'active', 'featured' => 'success',
            'pending'            => 'warning',
            'hired'              => 'info',
            'rejected'           => 'danger',
            default              => 'gray',
        };
    }

    public static function visaStatusLabel(?string $status): string
    {
        return match ($status) {
            'visit'      => 'ভিজিট ভিসা',
            'iqama'      => 'ইকামা (কর্মরত)',
            'free_exit'  => 'ফ্রি এক্সিট',
            'final_exit' => 'ফাইনাল এক্সিট',
            'new_visa'   => 'নতুন ভিসা প্রয়োজন',
            default      => '—',
        };
    }

    public static function languageLevelLabel(?string $level): string
    {
        return match ($level) {
            'none'         => 'জানে না',
            'basic'        => 'বেসিক',
            'intermediate' => 'মধ্যম',
            'fluent'       => 'সাবলীল',
            default        => '—',
        };
    }

    public static function educationLevelLabel(?string $level): string
    {
        return match ($level) {
            'none'      => 'কোনো শিক্ষা নেই',
            'primary'   => 'প্রাথমিক',
            'secondary' => 'মাধ্যমিক',
            'hsc'       => 'উচ্চ মাধ্যমিক',
            'degree'    => 'ডিগ্রি',
            default     => '—',
        };
    }
}