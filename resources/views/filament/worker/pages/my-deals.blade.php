<x-filament-panels::page>
    @if($this->deals->isEmpty())
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500">
            {{ __('messages.worker_my_deals.no_deals') }}
        </div>
    @endif

    <div class="space-y-6">
        @foreach($this->deals as $deal)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
                {{-- Deal Header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $deal->jobPost->job_title ?? __('messages.worker_my_deals.job_post_fallback') }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ __('messages.worker_my_deals.agent_label') }}: {{ $deal->agent->name ?? '—' }} &middot;
                            {{ __('messages.worker_my_deals.total_fee') }}: {{ number_format($deal->agent_fee_sar, 2) }} SAR
                        </p>
                    </div>
                    <x-filament::badge :color="match($deal->status) {
                        'confirmed' => 'info',
                        'working' => 'warning',
                        'completed' => 'success',
                        'disputed' => 'danger',
                        'cancelled', 'refunded' => 'gray',
                        default => 'gray',
                    }">
                        @switch($deal->status)
                            @case('confirmed') {{ __('messages.worker_my_deals.status_confirmed') }} @break
                            @case('working') {{ __('messages.worker_my_deals.status_working') }} @break
                            @case('completed') {{ __('messages.worker_my_deals.status_completed') }} @break
                            @case('disputed') {{ __('messages.worker_my_deals.status_disputed') }} @break
                            @case('cancelled') {{ __('messages.worker_my_deals.status_cancelled') }} @break
                            @case('refunded') {{ __('messages.worker_my_deals.status_refunded') }} @break
                            @default {{ $deal->status }}
                        @endswitch
                    </x-filament::badge>
                </div>

                {{-- Milestones --}}
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach($deal->milestones as $milestone)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-gray-500">
                                    {{ __('messages.worker_my_deals.milestone') }} {{ $milestone->milestone_number }}
                                </span>
                                <x-filament::badge :color="\App\Filament\Worker\Pages\MyDeals::statusColor($milestone->status)" size="sm">
                                    {{ \App\Filament\Worker\Pages\MyDeals::statusLabel($milestone->status) }}
                                </x-filament::badge>
                            </div>

                            <p class="text-sm font-medium text-gray-950 dark:text-white mb-1">
                                {{ $milestone->title }}
                            </p>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ $milestone->percentage }}% &middot; {{ __('messages.worker_my_deals.amount') }}: {{ number_format($milestone->amount_sar, 2) }} SAR
                            </p>

                            @if($milestone->status === 'pending')
                                <x-filament::button
                                    size="sm"
                                    color="primary"
                                    class="w-full"
                                    wire:click="openConfirmModal({{ $milestone->id }})"
                                >
                                    {{ __('messages.worker_my_deals.confirm_stage_done') }}
                                </x-filament::button>
                            @elseif($milestone->status === 'worker_confirmed')
                                <p class="text-xs text-info-600">{{ __('messages.worker_my_deals.waiting_agent_confirmation') }}</p>
                            @elseif($milestone->status === 'agent_confirmed')
                                <p class="text-xs text-warning-600">{{ __('messages.worker_my_deals.waiting_admin_release') }}</p>
                            @elseif($milestone->status === 'admin_released')
                                <p class="text-xs text-success-600 mb-2">
                                    {{ __('messages.worker_my_deals.payment_completed') }}
                                </p>
                                @if($milestone->receipt_path)
                                    <x-filament::button
                                        size="sm"
                                        color="gray"
                                        icon="heroicon-o-document-arrow-down"
                                        class="w-full"
                                        tag="a"
                                        href="{{ route('milestones.receipt.download', $milestone) }}"
                                        target="_blank"
                                    >
                                        {{ __('messages.worker_my_deals.download_receipt') }}
                                    </x-filament::button>
                                @endif
                            @elseif($milestone->status === 'disputed')
                                <p class="text-xs text-danger-600">{{ __('messages.worker_my_deals.milestone_disputed') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Confirm Modal --}}
    @if($confirmingMilestoneId)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4"
            wire:key="confirm-modal"
        >
            <div class="w-full max-w-sm rounded-xl bg-white dark:bg-gray-900 p-6 shadow-xl">
                <h4 class="text-base font-semibold text-gray-950 dark:text-white mb-2">
                    {{ __('messages.worker_my_deals.confirm_milestone_title') }}
                </h4>
                <p class="text-sm text-gray-500 mb-5">
                    {{ __('messages.worker_my_deals.confirm_milestone_desc') }}
                </p>
                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="closeConfirmModal">
                        {{ __('messages.worker_my_deals.cancel') }}
                    </x-filament::button>
                    <x-filament::button color="primary" wire:click="confirmMilestone">
                        {{ __('messages.worker_my_deals.yes_confirm') }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>