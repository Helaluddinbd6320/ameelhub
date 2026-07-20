<div class="space-y-3">
    @forelse ($results as $result)
        <div class="flex items-start justify-between gap-4 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
            <div>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    {{ $result['worker_name'] }}
                </p>
                @if ($result['status'] === 'failed')
                    <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">
                        {{ $result['reason'] }}
                    </p>
                @endif
            </div>

            <span @class([
                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium shrink-0',
                'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200' => $result['status'] === 'success',
                'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200' => $result['status'] === 'failed',
            ])>
                {{ $result['status'] === 'success' ? 'পাঠানো হয়েছে' : 'ব্যর্থ হয়েছে' }}
            </span>
        </div>
    @empty
        <p class="text-gray-500 dark:text-gray-400">কোনো ফলাফল নেই।</p>
    @endforelse
</div>
