<?php

namespace App\Filament\Worker\Pages;

use App\Models\JobDeal;
use App\Models\JobDealMilestone;
use App\Services\MilestoneService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class MyDeals extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.worker.pages.my-deals';

    /** মডাল কনফার্মেশনের জন্য নির্বাচিত মাইলস্টোন আইডি */
    public ?int $confirmingMilestoneId = null;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.navigation.groups.deals_payments');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_deals');
    }

    public function getTitle(): string
    {
        return __('messages.navigation.resources.my_deals');
    }

    public function getDealsProperty(): Collection
    {
        $worker = auth()->user()->worker;

        if (! $worker) {
            return collect();
        }

        return JobDeal::query()
            ->where('worker_id', $worker->id)
            ->with([
                'jobPost',
                'agent',
                'milestones' => fn ($q) => $q->orderBy('milestone_number'),
            ])
            ->latest()
            ->get();
    }

    public function openConfirmModal(int $milestoneId): void
    {
        $this->confirmingMilestoneId = $milestoneId;
    }

    public function closeConfirmModal(): void
    {
        $this->confirmingMilestoneId = null;
    }

    public function confirmMilestone(MilestoneService $service): void
    {
        if (! $this->confirmingMilestoneId) {
            return;
        }

        $milestone = JobDealMilestone::find($this->confirmingMilestoneId);

        if (! $milestone) {
            $this->confirmingMilestoneId = null;
            return;
        }

        try {
            $service->confirmByWorker($milestone, auth()->user());

            Notification::make()
                ->title('মাইলস্টোন কনফার্ম করা হয়েছে')
                ->body('আপনার কনফার্মেশন Agent এর কাছে পাঠানো হয়েছে।')
                ->success()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('কনফার্ম করা যায়নি')
                ->body(collect($e->errors())->flatten()->first() ?? 'একটি সমস্যা হয়েছে।')
                ->danger()
                ->send();
        }

        $this->confirmingMilestoneId = null;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'          => 'অপেক্ষমান — আপনার কনফার্মেশন প্রয়োজন',
            'worker_confirmed' => 'আপনি কনফার্ম করেছেন — Agent এর অপেক্ষায়',
            'agent_confirmed'  => 'Agent কনফার্ম করেছে — Admin রিলিজের অপেক্ষায়',
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
}