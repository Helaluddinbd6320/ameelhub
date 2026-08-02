<div>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
        <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">
            আপনার জন্য সেরা Job
        </h3>

        @php($jobs = $this->getRecommendedJobs())

        @if (empty($jobs))
            <p class="text-sm text-gray-500 dark:text-gray-400">
                এই মুহূর্তে আপনার প্রোফাইলের সাথে মিলে এমন কোনো Job পাওয়া যায়নি।
                আপনার CV-তে দক্ষতা (skill), প্রত্যাশিত বেতন ও বর্তমান অবস্থান সম্পূর্ণ থাকলে
                আরও ভালো Job suggestion পাবেন।
            </p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($jobs as $item)
                    @php
                        $job = $item['job'];
                        $score = $item['score'];
                        $badgeClasses = match (true) {
                            $score['total'] >= 75 => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400',
                            $score['total'] >= 50 => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                            default => 'bg-gray-100 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400',
                        };
                    @endphp
                    <a
                        href="{{ $job->public_url }}"
                        target="_blank"
                        rel="noopener"
                        class="block rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition"
                        title="দক্ষতা: {{ $score['skill'] }}/40 · অবস্থান: {{ $score['location'] }}/20 · বেতন: {{ $score['salary'] }}/20 · ভিসা: {{ $score['visa'] }}/20"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $badgeClasses }}">
                                {{ $score['total'] }}% ম্যাচ
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500 truncate ml-2">
                                {{ $job->employer_city }}
                            </span>
                        </div>
                        <h4 class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate">
                            {{ $job->job_title }}
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                            {{ $job->employer_name }}
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-2">
                            {{ number_format((float) $job->salary_sar, 0) }} SAR
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>