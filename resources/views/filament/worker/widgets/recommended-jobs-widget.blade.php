<div>
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">
            আপনার জন্য সেরা Job
        </h3>

        @php
            $jobs = $this->getRecommendedJobs();
        @endphp

        @if (count($jobs) === 0)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                এই মুহূর্তে আপনার প্রোফাইলের সাথে মিলে এমন কোনো Job পাওয়া যায়নি। আপনার CV সম্পূর্ণ থাকলে আরও ভালো ফলাফল পাবেন।
            </p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($jobs as $item)
                    @php
                        $job = $item['job'];
                        $score = $item['score'];
                        $total = $score['total'];

                        if ($total >= 75) {
                            $badgeClasses = 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20';
                        } elseif ($total >= 50) {
                            $badgeClasses = 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20';
                        } else {
                            $badgeClasses = 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-500/10 dark:text-gray-400 dark:ring-gray-500/20';
                        }
                    @endphp
                    <a
                        href="{{ $job->public_url }}"
                        target="_blank"
                        rel="noopener"
                        class="flex flex-col gap-2 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600 transition"
                    >
                        <div>
                            <span class="inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClasses }}">
                                {{ $total }}% match
                            </span>
                        </div>

                        <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 leading-snug">
                            {{ $job->job_title }}
                        </h4>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $job->employer_name }} &middot; {{ $job->employer_city }}
                        </p>

                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100 mt-auto pt-1">
                            {{ number_format((float) $job->salary_sar, 0) }} SAR
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>