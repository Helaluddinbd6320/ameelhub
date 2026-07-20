<x-filament-panels::page>
    <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
        @foreach ([
            'pending'  => 'অপেক্ষমান',
            'accepted' => 'গৃহীত',
            'rejected' => 'প্রত্যাখ্যাত',
            'expired'  => 'মেয়াদোত্তীর্ণ',
        ] as $key => $label)
            <button
                wire:click="setTab('{{ $key }}')"
                class="px-4 py-2 text-sm font-medium border-b-2 transition
                    {{ $tab === $key
                        ? 'border-primary-600 text-primary-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($this->selections as $selection)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900 shadow-sm">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        {{ $selection->jobPost?->job_title ?? 'Job মুছে ফেলা হয়েছে' }}
                    </h3>

                    <span @class([
                        'text-xs px-2 py-1 rounded-full font-medium',
                        'bg-warning-100 text-warning-700' => $selection->worker_response === 'pending',
                        'bg-success-100 text-success-700' => $selection->worker_response === 'accepted',
                        'bg-danger-100 text-danger-700'   => $selection->worker_response === 'rejected',
                        'bg-gray-100 text-gray-500'        => $selection->worker_response === 'expired',
                    ])>
                        {{ match($selection->worker_response) {
                            'pending'  => 'অপেক্ষমান',
                            'accepted' => 'গৃহীত',
                            'rejected' => 'প্রত্যাখ্যাত',
                            'expired'  => 'মেয়াদোত্তীর্ণ',
                            default    => $selection->worker_response,
                        } }}
                    </span>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    Employer: {{ $selection->jobPost?->employer_name ?? '—' }}
                </p>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    এজেন্ট ফি: <span class="font-medium text-gray-900 dark:text-white">{{ $selection->agent_fee_sar }} SAR</span>
                </p>

                <p class="text-xs text-gray-400 mb-3">
                    পাঠানো হয়েছে: {{ $selection->notification_sent_at?->diffForHumans() }}
                    @if ($selection->worker_response === 'pending')
                        <br>মেয়াদ শেষ: {{ $selection->expires_at?->diffForHumans() }}
                    @endif
                </p>

                @if ($selection->worker_response === 'pending')
                    <div class="flex gap-2 mt-3">
                        <button
                            wire:click="acceptSelection({{ $selection->id }})"
                            wire:confirm="নিশ্চিত করলে আপনার Wallet থেকে {{ $selection->agent_fee_sar }} SAR Escrow তে জমা (Hold) হবে। এগিয়ে যাবেন?"
                            class="flex-1 px-3 py-2 text-sm font-medium rounded-lg bg-success-600 text-white hover:bg-success-500 transition"
                        >
                            গ্রহণ করুন
                        </button>
                        <button
                            wire:click="rejectSelection({{ $selection->id }})"
                            wire:confirm="আপনি কি নিশ্চিত এই Job Offer প্রত্যাখ্যান করতে চান?"
                            class="flex-1 px-3 py-2 text-sm font-medium rounded-lg bg-danger-600 text-white hover:bg-danger-500 transition"
                        >
                            প্রত্যাখ্যান করুন
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                এই ট্যাবে কোনো Selection নেই।
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $this->selections->links() }}
    </div>
</x-filament-panels::page>