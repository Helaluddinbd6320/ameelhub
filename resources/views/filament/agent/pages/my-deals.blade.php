<x-filament-panels::page>
    @if($this->deals->isEmpty())
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500">
            আপনার এখনো কোনো নিশ্চিত ডিল (Deal) নেই।
        </div>
    @endif

    <div class="space-y-6">
        @foreach($this->deals as $deal)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
                {{-- Deal Header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $deal->jobPost->job_title ?? 'জব পোস্ট' }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Worker: {{ $deal->worker->full_name_bn ?? $deal->worker->full_name_en ?? '—' }} &middot;
                            মোট ফি: {{ number_format($deal->agent_fee_sar, 2) }} SAR &middot;
                            আপনার প্রাপ্য: {{ number_format($deal->agent_receives_sar, 2) }} SAR
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
                            @case('confirmed') নিশ্চিত হয়েছে @break
                            @case('working') কাজ চলমান @break
                            @case('completed') সম্পন্ন ✅ @break
                            @case('disputed') বিরোধ চলমান @break
                            @case('cancelled') বাতিল @break
                            @case('refunded') রিফান্ড হয়েছে @break
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
                                    মাইলস্টোন {{ $milestone->milestone_number }}
                                </span>
                                <x-filament::badge :color="\App\Filament\Agent\Pages\MyDeals::statusColor($milestone->status)" size="sm">
                                    {{ \App\Filament\Agent\Pages\MyDeals::statusLabel($milestone->status) }}
                                </x-filament::badge>
                            </div>

                            <p class="text-sm font-medium text-gray-950 dark:text-white mb-1">
                                {{ $milestone->title }}
                            </p>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ $milestone->percentage }}% &middot; আপনার প্রাপ্য: {{ number_format($milestone->agent_receives_sar, 2) }} SAR
                            </p>

                            @if($milestone->status === 'worker_confirmed')
                                <x-filament::button
                                    size="sm"
                                    color="primary"
                                    class="w-full"
                                    wire:click="openConfirmModal({{ $milestone->id }})"
                                >
                                    Worker এর কনফার্মেশন গ্রহণ করুন
                                </x-filament::button>
                            @elseif($milestone->status === 'pending')
                                <p class="text-xs text-gray-500">Worker এর কনফার্মেশনের অপেক্ষায়</p>
                            @elseif($milestone->status === 'agent_confirmed')
                                <p class="text-xs text-warning-600">Admin এর পেমেন্ট রিলিজের অপেক্ষায়</p>
                            @elseif($milestone->status === 'admin_released')
                                <p class="text-xs text-success-600 mb-2">
                                    পেমেন্ট পরিশোধ সম্পন্ন
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
                                        রশিদ ডাউনলোড
                                    </x-filament::button>
                                @endif
                            @elseif($milestone->status === 'disputed')
                                <p class="text-xs text-danger-600">এই মাইলস্টোনে বিরোধ চলছে</p>
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
                    মাইলস্টোন কনফার্ম করুন
                </h4>
                <p class="text-sm text-gray-500 mb-5">
                    Worker এই ধাপের কাজ সম্পন্ন হয়েছে বলে কনফার্ম করেছেন। আপনি কি এটি নিশ্চিত করছেন?
                    কনফার্ম করার পর এটি Admin এর কাছে পেমেন্ট রিলিজের জন্য পাঠানো হবে।
                </p>
                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="closeConfirmModal">
                        বাতিল
                    </x-filament::button>
                    <x-filament::button color="primary" wire:click="confirmMilestone">
                        হ্যাঁ, কনফার্ম করছি
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>