<x-filament-panels::page>
    {{-- Tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach ([
            'pending'  => __('messages.nok_page.status_pending'),
            'accepted' => __('messages.nok_page.status_accepted'),
            'rejected' => __('messages.nok_page.status_rejected'),
            'expired'  => __('messages.nok_page.status_expired'),
        ] as $status => $label)
            <button
                type="button"
                wire:click="setTab('{{ $status }}')"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    'bg-primary-600 text-white' => $tab === $status,
                    'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' => $tab !== $status,
                ])
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Nok Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($this->noks as $nok)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900 shadow-sm">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ $nok->jobPost?->job_title ?? __('messages.nok_page.job_deleted') }}
                    </h3>
                    <span @class([
                        'text-xs px-2 py-1 rounded-full font-medium',
                        'bg-amber-100 text-amber-800' => $nok->status === 'pending',
                        'bg-green-100 text-green-800' => $nok->status === 'accepted',
                        'bg-red-100 text-red-800' => $nok->status === 'rejected',
                        'bg-gray-200 text-gray-600' => $nok->status === 'expired',
                    ])>
                        {{ match($nok->status) {
                            'pending' => __('messages.nok_page.status_pending'),
                            'accepted' => __('messages.nok_page.status_accepted'),
                            'rejected' => __('messages.nok_page.status_rejected'),
                            'expired' => __('messages.nok_page.status_expired'),
                        } }}
                    </span>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    {{ __('messages.nok_page.employer_label') }}: {{ $nok->jobPost?->employer_name ?? '—' }} ({{ $nok->jobPost?->employer_city ?? '—' }})
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                    {{ __('messages.nok_page.agent_label') }}: {{ $nok->agent?->name ?? __('messages.nok_page.unknown') }}
                </p>

                @if ($nok->nok_message)
                    <p class="text-sm italic text-gray-500 dark:text-gray-400 mt-2 border-l-2 border-gray-300 dark:border-gray-700 pl-2">
                        "{{ $nok->nok_message }}"
                    </p>
                @endif

                <div class="text-xs text-gray-400 mt-3 space-y-0.5">
                    <p>{{ __('messages.nok_page.sent_at') }}: {{ $nok->sent_at->translatedFormat('d M Y, h:i A') }}</p>
                    @if ($nok->status === 'pending')
                        <p>{{ __('messages.nok_page.expires_at') }}: {{ $nok->expires_at->translatedFormat('d M Y, h:i A') }}</p>
                    @elseif ($nok->responded_at)
                        <p>{{ __('messages.nok_page.responded_at') }}: {{ $nok->responded_at->translatedFormat('d M Y, h:i A') }}</p>
                    @endif
                </div>

                @if ($nok->status === 'pending')
                    <div class="flex gap-2 mt-4">
                        <button
                            type="button"
                            wire:click="acceptNok({{ $nok->id }})"
                            wire:confirm="{{ __('messages.nok_page.confirm_accept') }}"
                            class="flex-1 px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium"
                        >
                            {{ __('messages.nok_page.accept') }}
                        </button>
                        <button
                            type="button"
                            wire:click="rejectNok({{ $nok->id }})"
                            wire:confirm="{{ __('messages.nok_page.confirm_reject') }}"
                            class="flex-1 px-3 py-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium"
                        >
                            {{ __('messages.nok_page.reject') }}
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                {{ __('messages.nok_page.no_noks_in_category') }}
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $this->noks->links() }}
    </div>
</x-filament-panels::page>