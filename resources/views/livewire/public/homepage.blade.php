<div class="max-w-6xl mx-auto px-4 py-8 space-y-10" style="font-family: 'Hind Siliguri', sans-serif;">

    {{-- ============ HERO (compact) ============ --}}
    <div class="relative overflow-hidden rounded-2xl bg-[#0B4F3F] px-5 py-5 sm:px-8 sm:py-6">
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
             style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;"></div>

        <div class="relative flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">

            {{-- seal icon, small, inline --}}
            <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#C9974C]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#0B4F3F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M12 2 4 5v6c0 5 3.4 8.7 8 10 4.6-1.3 8-5 8-10V5l-8-3Z" stroke-linejoin="round"/>
                    <path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            {{-- text --}}
            <div class="flex-1 min-w-0">
                <h1 class="text-lg sm:text-xl font-bold text-white leading-tight" style="font-family: 'Noto Serif Bengali', serif;">
                    আপনার টাকা আমাদের কাছে নিরাপদ
                </h1>
                <p class="text-[13px] text-[#CFE3D9] mt-0.5">
                    কাজ বুঝে পেলে Agent পাবে — মাইলস্টোন ভিত্তিক এস্ক্রো সিস্টেম
                    <span class="text-[#C9974C] hidden sm:inline">· লাইসেন্স নং ০০১৬২০৫</span>
                </p>
            </div>

            {{-- buttons --}}
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('workers.index') }}"
                   class="px-4 py-2 rounded-lg bg-[#C9974C] text-[#0B4F3F] text-sm font-semibold hover:bg-[#dbab5e] transition whitespace-nowrap">
                    কর্মী খুঁজুন
                </a>
                <a href="{{ route('jobs.index') }}"
                   class="px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white text-sm font-medium hover:bg-white/20 transition whitespace-nowrap">
                    জব দেখুন
                </a>
            </div>
        </div>
    </div>

    {{-- ============ FEATURED CVs ============ --}}
    @if ($featuredWorkers->isNotEmpty())
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="text-[#C9974C]">★</span>
                <h2 class="text-lg font-bold text-gray-900" style="font-family: 'Noto Serif Bengali', serif;">
                    ফিচারড কর্মী
                </h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Step 10.8e Fix: Featured cards সবসময় hero-এর পরে fold-এর কাছাকাছি/উপরে থাকে,
                     তাই সবগুলো eager রাখা হলো (সাধারণত ৬টার বেশি না, ক্ষতি নেই) --}}
                @foreach ($featuredWorkers as $worker)
                    <a href="{{ route('workers.show', $worker->uuid) }}"
                       class="group relative bg-white rounded-2xl border border-[#C9974C]/40 hover:border-[#C9974C] hover:shadow-lg hover:shadow-[#C9974C]/10 transition p-4 flex gap-4">
                        <span class="absolute -top-2 -right-2 bg-[#C9974C] text-white text-[10px] font-semibold px-2 py-1 rounded-full">
                            ফিচারড
                        </span>
                        @if ($worker->photo)
                            <img src="{{ asset('storage/' . $worker->photo) }}"
                                 alt="{{ $worker->full_name_bn }}"
                                 width="64"
                                 height="64"
                                 loading="eager"
                                 decoding="async"
                                 class="w-16 h-16 rounded-xl object-cover shrink-0 ring-2 ring-[#C9974C]/30">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-[#0B4F3F]/5 flex items-center justify-center text-gray-400 text-xs shrink-0">
                                ছবি নেই
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $worker->full_name_bn }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ $worker->skillCategory?->name_bn }}</p>
                            <p class="text-sm font-medium text-[#0B4F3F] mt-1.5">{{ $worker->expected_salary_sar }} SAR</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ============ LATEST WORKERS ============ --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900" style="font-family: 'Noto Serif Bengali', serif;">
                সাম্প্রতিক কর্মী
            </h2>
            <a href="{{ route('workers.index') }}" class="text-sm text-[#0B4F3F] font-medium hover:underline">
                সব দেখুন →
            </a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Step 10.8e Fix: এই সেকশন Featured-এর নিচে (fold-এর নিচে থাকতে পারে),
                 তাই lazy loading — page-load-এ শুধু browser যেটা viewport-এ দেখাবে সেটাই লোড হবে --}}
            @forelse ($latestWorkers as $worker)
                <a href="{{ route('workers.show', $worker->uuid) }}"
                   class="group bg-white rounded-2xl border border-gray-100 hover:border-[#0B4F3F]/20 hover:shadow-md transition p-4">
                    @if ($worker->photo)
                        <img src="{{ asset('storage/' . $worker->photo) }}"
                             alt="{{ $worker->full_name_bn }}"
                             width="400"
                             height="128"
                             loading="lazy"
                             decoding="async"
                             class="w-full h-32 rounded-xl object-cover mb-3 group-hover:opacity-90 transition">
                    @else
                        <div class="w-full h-32 rounded-xl bg-[#0B4F3F]/5 flex items-center justify-center text-gray-400 text-xs mb-3">
                            ছবি নেই
                        </div>
                    @endif
                    <p class="font-semibold text-gray-900 truncate">{{ $worker->full_name_bn }}</p>
                    <p class="text-sm text-gray-500 truncate">{{ $worker->skillCategory?->name_bn }}</p>
                    <p class="text-sm font-medium text-[#0B4F3F] mt-1">{{ $worker->expected_salary_sar }} SAR</p>
                </a>
            @empty
                <p class="col-span-full text-center text-gray-400 py-10">কোনো কর্মী পাওয়া যায়নি।</p>
            @endforelse
        </div>
    </div>

    {{-- ============ LATEST JOBS ============ --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900" style="font-family: 'Noto Serif Bengali', serif;">
                সাম্প্রতিক জব
            </h2>
            <a href="{{ route('jobs.index') }}" class="text-sm text-[#0B4F3F] font-medium hover:underline">
                সব দেখুন →
            </a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse ($latestJobs as $job)
                <a href="{{ route('jobs.show', $job->uuid) }}"
                   class="bg-white rounded-2xl border border-gray-100 hover:border-[#0B4F3F]/20 hover:shadow-md transition p-4">
                    <p class="font-semibold text-gray-900 truncate">{{ $job->job_title }}</p>
                    <p class="text-sm text-gray-500 truncate">{{ $job->employer_name }} — {{ $job->employer_city }}</p>
                    <p class="text-sm font-medium text-[#0B4F3F] mt-1.5">{{ $job->salary_sar }} SAR</p>
                    <div class="mt-2 pt-2 border-t border-gray-50">
                        <p class="text-xs text-gray-400">
                            {{ $job->vacancies - $job->filled_count }} / {{ $job->vacancies }} শূন্যপদ বাকি
                        </p>
                    </div>
                </a>
            @empty
                <p class="col-span-full text-center text-gray-400 py-10">কোনো জব পাওয়া যায়নি।</p>
            @endforelse
        </div>
    </div>

</div>