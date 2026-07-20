{{-- resources/views/livewire/public/agent-profile-page.blade.php --}}
<div class="max-w-4xl mx-auto px-4 py-8">

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">

        {{-- Gradient Cover --}}
        <div class="relative" style="height: 140px; background: linear-gradient(to right, #10b981, #059669, #0d9488);">
            @if ($agentProfile->is_verified)
                <span class="absolute right-4 top-4 inline-flex items-center gap-1 bg-white/95 backdrop-blur text-emerald-700 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    যাচাইকৃত এজেন্ট
                </span>
            @endif
        </div>

        {{-- সাদা কনটেন্ট অংশ --}}
        <div class="px-6 pb-6">

            {{-- Avatar: normal flow-এ, negative margin দিয়ে কভারের উপর তুলে দেওয়া হয়েছে।
                 এর নিচের কনটেন্ট (h1) স্বয়ংক্রিয়ভাবেই এই div-এর পরে বসবে, ম্যানুয়াল পিক্সেল হিসাব লাগে না। --}}
            <div style="margin-top: -48px;" class="mb-4 inline-block">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($agentProfile->agent_name_en ?? $agentProfile->agent_name_bn ?? 'Agent') }}&size=128&background=065f46&color=fff&bold=true"
                    alt="{{ $agentProfile->agent_name_bn ?? $agentProfile->agent_name_en }}"
                    class="block w-24 h-24 sm:w-28 sm:h-28 rounded-full border-4 border-white shadow-lg object-cover bg-emerald-700"
                >
            </div>

            {{-- নাম --}}
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $agentProfile->agent_name_bn ?? $agentProfile->agent_name_en ?? 'নাম নেই' }}
            </h1>
            @if ($agentProfile->agent_name_en && $agentProfile->agent_name_bn)
                <p class="text-sm text-gray-500 mt-0.5">{{ $agentProfile->agent_name_en }}</p>
            @endif

            {{-- Info Badges --}}
            <div class="mt-3 flex flex-wrap gap-2 text-sm text-gray-600">
                @if ($agentProfile->company_name)
                    <span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1.5 rounded-full">
                        🏢 {{ $agentProfile->company_name }}
                    </span>
                @endif
                @if ($agentProfile->city)
                    <span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1.5 rounded-full">
                        📍 {{ $agentProfile->city }}, {{ $agentProfile->country }}
                    </span>
                @endif
                @if ($agentProfile->years_in_business)
                    <span class="inline-flex items-center gap-1.5 bg-gray-100 px-3 py-1.5 rounded-full">
                        📅 {{ $agentProfile->years_in_business }} বছরের অভিজ্ঞতা
                    </span>
                @endif
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-7">
                <div class="text-center bg-emerald-50 rounded-xl py-4 border border-emerald-100">
                    <p class="text-2xl font-bold text-emerald-700">{{ $agentProfile->successful_deals }}</p>
                    <p class="text-xs text-gray-600 mt-1">সফল ডিল</p>
                </div>
                <div class="text-center bg-emerald-50 rounded-xl py-4 border border-emerald-100">
                    <p class="text-2xl font-bold text-emerald-700">{{ $agentProfile->total_workers_placed }}</p>
                    <p class="text-xs text-gray-600 mt-1">প্লেসড কর্মী</p>
                </div>
                <div class="text-center bg-emerald-50 rounded-xl py-4 border border-emerald-100">
                    <p class="text-2xl font-bold text-emerald-700">{{ $agentProfile->total_jobs_posted }}</p>
                    <p class="text-xs text-gray-600 mt-1">মোট জব পোস্ট</p>
                </div>
                <div class="text-center bg-emerald-50 rounded-xl py-4 border border-emerald-100">
                    <p class="text-2xl font-bold text-emerald-700">{{ $activeJobPosts->count() }}</p>
                    <p class="text-xs text-gray-600 mt-1">সক্রিয় জব পোস্ট</p>
                </div>
            </div>

            {{-- ⚠️ এখানে কখনো phone_office, whatsapp_number, NID, Passport দেখানো যাবে না --}}

            {{-- Active Job Posts --}}
            <div class="mt-8">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">সক্রিয় জব পোস্ট</h2>
                    @if ($activeJobPosts->count() > 0)
                        <span class="text-xs bg-emerald-100 text-emerald-700 font-semibold px-2 py-0.5 rounded-full">
                            {{ $activeJobPosts->count() }}
                        </span>
                    @endif
                </div>

                @forelse ($activeJobPosts as $job)
                    <div class="border border-gray-200 rounded-xl p-4 mb-3 hover:border-emerald-300 hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $job->job_title }}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    📍 {{ $job->employer_city }} &nbsp;·&nbsp; 👥 {{ $job->vacancies }} জন
                                </p>
                            </div>
                            <span class="shrink-0 text-sm font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">
                                {{ number_format($job->salary_sar) }} SAR
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 bg-gray-50 rounded-xl">
                        <p class="text-sm text-gray-400">এই মুহূর্তে কোনো সক্রিয় জব পোস্ট নেই।</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>