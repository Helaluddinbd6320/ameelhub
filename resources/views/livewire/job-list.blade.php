<div>
    <div class="max-w-7xl mx-auto px-4 py-8">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight" style="font-family: 'Noto Serif Bengali', serif;">
                    জব লিস্ট
                </h1>
                <p class="text-sm text-gray-400 mt-0.5">Job Listings — সৌদি আরবের সকল সুযোগ এক জায়গায়</p>
            </div>

            <button
                wire:click="reshuffle"
                class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 shadow-sm hover:shadow-md transition-all duration-200"
                style="--hover-border:#C9974C;"
                onmouseover="this.style.borderColor='#C9974C'; this.style.color='#0B4F3F'"
                onmouseout="this.style.borderColor='#e5e7eb'; this.style.color='#374151'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                নতুন করে দেখুন
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            {{-- FILTER SIDEBAR --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5 sticky top-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color:#C9974C;" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                        </svg>
                        <h2 class="font-semibold text-gray-800 text-sm">ফিল্টার</h2>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">পেশা / Skill</label>
                        <select
                            wire:model.live="skillCategoryId"
                            class="w-full rounded-lg border-gray-200 text-sm text-gray-700"
                            style="--tw-ring-color:#0B4F3F;"
                            onfocus="this.style.borderColor='#0B4F3F'"
                            onblur="this.style.borderColor='#e5e7eb'"
                        >
                            <option value="">সব পেশা</option>
                            @foreach ($skillCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name_bn }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">শহর / City</label>
                        <input
                            type="text"
                            wire:model.live.debounce.400ms="employerCity"
                            placeholder="যেমন: Riyadh, Jeddah"
                            class="w-full rounded-lg border-gray-200 text-sm placeholder:text-gray-300"
                            onfocus="this.style.borderColor='#0B4F3F'"
                            onblur="this.style.borderColor='#e5e7eb'"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">বেতন সীমা (SAR)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                wire:model.live.debounce.400ms="minSalary"
                                placeholder="সর্বনিম্ন"
                                class="w-full rounded-lg border-gray-200 text-sm placeholder:text-gray-300"
                                onfocus="this.style.borderColor='#0B4F3F'"
                                onblur="this.style.borderColor='#e5e7eb'"
                            />
                            <input
                                type="number"
                                wire:model.live.debounce.400ms="maxSalary"
                                placeholder="সর্বোচ্চ"
                                class="w-full rounded-lg border-gray-200 text-sm placeholder:text-gray-300"
                                onfocus="this.style.borderColor='#0B4F3F'"
                                onblur="this.style.borderColor='#e5e7eb'"
                            />
                        </div>
                    </div>

                    <div class="space-y-2.5 pt-1">
                        <label class="flex items-center gap-2.5 text-sm text-gray-600 cursor-pointer group">
                            <input type="checkbox" wire:model.live="accommodationOnly" class="rounded border-gray-300" style="accent-color:#0B4F3F;">
                            <span class="group-hover:text-gray-900 transition">আবাসন সহ</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-600 cursor-pointer group">
                            <input type="checkbox" wire:model.live="foodOnly" class="rounded border-gray-300" style="accent-color:#0B4F3F;">
                            <span class="group-hover:text-gray-900 transition">খাবার সহ</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-600 cursor-pointer group">
                            <input type="checkbox" wire:model.live="transportOnly" class="rounded border-gray-300" style="accent-color:#0B4F3F;">
                            <span class="group-hover:text-gray-900 transition">যানবাহন সহ</span>
                        </label>
                    </div>

                    <button
                        wire:click="resetFilters"
                        class="w-full text-sm font-medium py-2.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition"
                    >
                        ফিল্টার রিসেট করুন
                    </button>
                </div>
            </div>

            {{-- JOB CARDS --}}
            <div class="lg:col-span-3">

                {{-- LOADING SKELETON --}}
                <div wire:loading class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 animate-pulse">
                            <div class="h-4 bg-gray-100 rounded w-2/3 mb-3"></div>
                            <div class="h-3 bg-gray-100 rounded w-1/2 mb-4"></div>
                            <div class="h-3 bg-gray-100 rounded w-1/3 mb-4"></div>
                            <div class="h-2 bg-gray-100 rounded w-full mb-4"></div>
                            <div class="h-9 bg-gray-100 rounded-xl w-full"></div>
                        </div>
                    @endfor
                </div>

                <div wire:loading.remove class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($jobs as $job)
                        <div
                            wire:key="job-{{ $job->id }}"
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-lg transition-all duration-200 flex flex-col"
                            onmouseover="this.style.borderColor='rgba(201,151,76,0.35)'"
                            onmouseout="this.style.borderColor='#f3f4f6'"
                        >
                            <div class="flex justify-between items-start gap-3 mb-2">
                                <h3 class="font-semibold text-gray-900 leading-snug">{{ $job->job_title }}</h3>
                                <span class="shrink-0 text-[11px] font-medium px-2.5 py-1 rounded-full whitespace-nowrap" style="background-color:rgba(11,79,63,0.07); color:#0B4F3F;">
                                    {{ $job->skillCategory?->name_bn }}
                                </span>
                            </div>

                            <p class="flex items-center gap-1.5 text-sm text-gray-400 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                {{ $job->employer_name }} • {{ $job->employer_city }}, {{ $job->employer_country }}
                            </p>

                            <p class="text-sm text-gray-500 mb-3">
                                বেতন
                                <span class="font-semibold text-gray-900 text-base ml-1">{{ number_format($job->salary_sar, 2) }}</span>
                                <span class="text-xs text-gray-400">SAR / মাস</span>
                            </p>

                            <div class="flex flex-wrap gap-1.5 mb-4">
                                @if ($job->accommodation)
                                    <span class="text-[11px] font-medium bg-blue-50 text-blue-600 px-2.5 py-1 rounded-full">আবাসন</span>
                                @endif
                                @if ($job->food_included)
                                    <span class="text-[11px] font-medium bg-green-50 text-green-600 px-2.5 py-1 rounded-full">খাবার</span>
                                @endif
                                @if ($job->transport_provided)
                                    <span class="text-[11px] font-medium bg-purple-50 text-purple-600 px-2.5 py-1 rounded-full">যানবাহন</span>
                                @endif
                            </div>

                            <div class="mt-auto space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <x-job-vacancy-counter :job="$job" class="flex-1" />
                                    <x-job-expiry-counter :job="$job" />
                                </div>

                                <a href="{{ Route::has('jobs.show') ? route('jobs.show', $job->uuid) : '#' }}"
                                    class="flex items-center justify-center gap-1.5 text-sm font-semibold rounded-xl py-2.5 active:scale-[0.98] transition-all duration-150"
                                    style="background-color:#0B4F3F; color:#ffffff;"
                                    onmouseover="this.style.backgroundColor='#0e6350'"
                                    onmouseout="this.style.backgroundColor='#0B4F3F'"
                                >
                                    বিস্তারিত দেখুন
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 flex flex-col items-center justify-center text-center py-20 bg-white rounded-2xl border border-dashed border-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-200 mb-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                            </svg>
                            <p class="text-gray-400 text-sm">কোনো জব পাওয়া যায়নি।</p>
                            <p class="text-gray-300 text-xs mt-1">ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $jobs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>