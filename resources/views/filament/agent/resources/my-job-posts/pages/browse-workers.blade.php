<x-filament-panels::page>
    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
            Job: {{ $this->record->job_title }} — {{ $this->record->employer_name }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400">
            ভ্যাকান্সি: {{ $this->record->filled_count }}/{{ $this->record->vacancies }}
        </p>
    </div>

    {{ $this->table }}

    @if ($showBulkNokResultModal)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/50 p-4"
            wire:key="bulk-nok-result-backdrop"
        >
            <div class="w-full max-w-lg rounded-xl bg-white shadow-xl dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Bulk Nok ফলাফল — {{ $this->getBulkNokSuccessCount() }} জন সফল, {{ $this->getBulkNokFailedCount() }} জন ব্যর্থ
                    </h2>
                    <button
                        type="button"
                        wire:click="closeBulkNokResultModal"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto px-6 py-4">
                    <div class="space-y-3">
                        @forelse ($bulkNokResults as $result)
                            <div class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $result['worker_name'] }}
                                    </p>
                                    @if ($result['status'] === 'failed')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">
                                            {{ $result['reason'] }}
                                        </p>
                                    @endif
                                </div>

                                <span @class([
                                    'inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $result['status'] === 'success',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $result['status'] === 'failed',
                                ])>
                                    {{ $result['status'] === 'success' ? 'পাঠানো হয়েছে' : 'ব্যর্থ হয়েছে' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">কোনো ফলাফল নেই।</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button
                        type="button"
                        wire:click="closeBulkNokResultModal"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        বন্ধ করুন
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>