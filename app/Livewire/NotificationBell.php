<?php

namespace App\Livewire;

use App\Models\AppNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    /**
     * Recompute unread count. Also listenable so other components
     * (e.g. after a job dispatches a notification) can trigger a refresh.
     */
    #[On('notification-refresh')]
    public function refreshCount(): void
    {
        $this->unreadCount = AppNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Latest 10 notifications for the dropdown list.
     */
    public function getNotificationsProperty()
    {
        return AppNotification::query()
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->limit(10)
            ->get();
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = AppNotification::query()
            ->where('user_id', auth()->id())
            ->findOrFail($notificationId);

        if (! $notification->isRead()) {
            $notification->markAsRead();
        }

        $this->refreshCount();

        $url = data_get($notification->data, 'url');

        if (filled($url)) {
            $this->redirect($url, navigate: false);
        }
    }

    public function markAllAsRead(): void
    {
        AppNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->refreshCount();
    }

    public function render()
    {
        return view('livewire.notification-bell', [
            'notifications' => $this->notifications,
        ]);
    }
}