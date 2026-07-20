<?php

namespace App\Filament\Agent\Pages;

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

    protected string $view = 'filament.agent.pages.my-deals';

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
        return JobDeal::query()
            ->where('agent_id', auth()->id())
            ->with([
                'jobPost',
                'worker',
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
            $service->confirmByAgent($milestone, auth()->user());

            Notification::make()
                ->title('মাইলস্টোন কনফার্ম করা হয়েছে')
                ->body('আপনার কনফার্মেশন Admin এর কাছে পাঠানো হয়েছে — পেমেন্ট রিলিজের অপেক্ষায়।')
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
            'pending'          => 'অপেক্ষমান',
            'worker_confirmed' => 'Worker কনফার্ম করেছে',
            'agent_confirmed'  => 'আপনি কনফার্ম করেছেন',
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