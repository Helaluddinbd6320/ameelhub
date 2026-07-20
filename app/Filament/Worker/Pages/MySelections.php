<?php

namespace App\Filament\Worker\Pages;

use App\Models\JobSelection;
use App\Models\Worker;
use App\Services\JobSelectionService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;
use UnitEnum;

class MySelections extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected string $view = 'filament.worker.pages.my-selections';

    public Worker $worker;

    public string $tab = 'pending';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.noks_selections');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_selections');
    }

    public function getTitle(): string
    {
        return __('messages.navigation.resources.my_selections');
    }

    public function mount(): void
    {
        $this->worker = Worker::where('worker_user_id', Auth::id())->firstOrFail();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function getSelectionsProperty()
    {
        return JobSelection::query()
            ->where('worker_id', $this->worker->id)
            ->where('worker_response', $this->tab)
            ->with(['jobPost', 'agent'])
            ->latest('notification_sent_at')
            ->paginate(9);
    }

    public function acceptSelection(int $selectionId): void
    {
        try {
            $deal = app(JobSelectionService::class)->accept($selectionId, Auth::user());

            Notification::make()
                ->title('অভিনন্দন! আপনি Job গ্রহণ করেছেন')
                ->body("Escrow Deal #{$deal->id} তৈরি হয়েছে। আপনার Wallet এর {$deal->agent_fee_sar} SAR এখন Held আছে।")
                ->success()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('গ্রহণ করা যায়নি')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public function rejectSelection(int $selectionId): void
    {
        try {
            app(JobSelectionService::class)->reject($selectionId, Auth::user());

            Notification::make()
                ->title('Selection প্রত্যাখ্যান করা হয়েছে')
                ->success()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('প্রত্যাখ্যান করা যায়নি')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }

    public static function getNavigationBadge(): ?string
    {
        $worker = Worker::where('worker_user_id', Auth::id())->first();

        if (! $worker) {
            return null;
        }

        $count = JobSelection::where('worker_id', $worker->id)
            ->where('worker_response', 'pending')
            ->where('expires_at', '>', now())
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}