<?php

namespace App\Filament\Worker\Pages;

use App\Models\AgentNok;
use App\Models\Worker;
use App\Services\NokService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;
use UnitEnum;

class MyNoks extends Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';

    protected string $view = 'filament.worker.pages.my-noks';

    public Worker $worker;

    public string $tab = 'pending';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.noks_selections');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_noks');
    }

    public function getTitle(): string
    {
        return __('messages.navigation.resources.my_noks');
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

    public function getNoksProperty()
    {
        return AgentNok::query()
            ->where('worker_id', $this->worker->id)
            ->where('status', $this->tab)
            ->with(['jobPost', 'agent'])
            ->latest('sent_at')
            ->paginate(9);
    }

    public function acceptNok(int $nokId): void
    {
        try {
            app(NokService::class)->accept($nokId, Auth::user());

            Notification::make()
                ->title('আপনি এই Job Offer গ্রহণ করেছেন')
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

    public function rejectNok(int $nokId): void
    {
        try {
            app(NokService::class)->reject($nokId, Auth::user());

            Notification::make()
                ->title('Nok প্রত্যাখ্যান করা হয়েছে')
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

        $count = AgentNok::where('worker_id', $worker->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}