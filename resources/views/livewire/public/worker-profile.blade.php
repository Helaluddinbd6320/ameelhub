<div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 py-10">
    <div class="max-w-4xl mx-auto px-4">

        {{-- Back Button --}}
        <a href="{{ route('workers.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-900 mb-5 transition-colors group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            তালিকায় ফিরে যান
        </a>

        {{-- Flash Messages --}}
        @if (session('error'))
            <div class="mb-4 rounded-2xl bg-red-50 border border-red-100 text-red-700 px-4 py-3 text-sm flex items-center gap-2">
                <span>⚠️</span> {{ session('error') }}
            </div>
        @endif

        @if ($nokSuccess)
            <div class="mb-4 rounded-2xl bg-green-50 border border-green-100 text-green-700 px-4 py-3 text-sm flex items-center gap-2">
                <span>✅</span> {{ $nokSuccess }}
            </div>
        @endif

        {{-- Main Worker Profile Card --}}
        <div class="bg-white rounded-[28px] shadow-[0_8px_30px_rgb(0,0,0,0.06)] ring-1 ring-slate-100 overflow-hidden">

            {{-- Cover Strip --}}
            <div class="h-24 relative" style="background: linear-gradient(120deg, #0B4F3F 0%, #14684F 55%, #1c7a5e 100%);">
                <div class="absolute inset-0 opacity-20" style="background-image:radial-gradient(circle at 20% 30%, white 0%, transparent 40%), radial-gradient(circle at 80% 70%, white 0%, transparent 35%)"></div>
            </div>

            {{-- Profile Header --}}
            <div class="relative z-10 px-6 sm:px-8 pb-6 -mt-10">
                <div class="flex flex-col sm:flex-row sm:items-end gap-5">
                    <div class="shrink-0">
                        @if ($worker->photo)
                            <img src="{{ asset('storage/' . $worker->photo) }}"
                                 alt="{{ $worker->full_name_bn }}"
                                 class="w-28 h-28 rounded-3xl object-cover ring-4 ring-white shadow-lg bg-white relative z-10">
                        @else
                            <div class="w-28 h-28 rounded-3xl bg-slate-100 ring-4 ring-white shadow-lg flex items-center justify-center text-slate-400 text-xs font-medium relative z-10">
                                ছবি নেই
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 sm:pb-1">
                        <div class="flex items-start justify-between flex-wrap gap-2">
                            <div>
                                <h1 class="text-2xl font-bold text-slate-900 tracking-tight" style="font-family: 'Noto Serif Bengali', serif;">{{ $worker->full_name_bn }}</h1>
                                <p class="text-slate-400 text-sm font-medium mt-0.5">{{ $worker->full_name_en }}</p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($worker->status === 'featured')
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold shadow-sm ring-1 ring-amber-200/50">
                                        ⭐ FEATURED
                                    </span>
                                @endif

                                @if ($this->isAgent)
                                    <button wire:click="openNokModal"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-orange-500 hover:bg-orange-600 active:scale-95 text-white text-sm font-bold shadow-sm shadow-orange-500/30 transition-all">
                                        📨 Job Offer পাঠাই
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @guest
                    <div class="mt-4 flex items-center justify-between rounded-2xl bg-orange-50 px-4 py-3.5 ring-1 ring-orange-100/60">
                        <p class="text-sm text-orange-700 font-medium">Agent হলে লগইন করে এই Worker কে সরাসরি Job Offer পাঠান।</p>
                        <a href="{{ route('login') }}" class="text-sm font-bold text-orange-700 hover:text-orange-900 shrink-0 transition-colors">
                            লগইন করুন →
                        </a>
                    </div>
                @endguest

                {{-- Badges --}}
                <div class="mt-5 flex flex-wrap gap-2">
                    @if ($worker->skillCategory)
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-800 text-sm font-semibold ring-1 ring-emerald-100">
                            {{ $worker->skillCategory->name_bn }}
                        </span>
                    @endif

                    @if ($worker->is_in_saudi)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-green-50 text-green-700 text-sm font-semibold ring-1 ring-green-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            সৌদিতে আছেন
                        </span>
                    @else
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-600 text-sm font-semibold ring-1 ring-slate-200">
                            বাংলাদেশে আছেন
                        </span>
                    @endif

                    @if ($this->iqamaStatus)
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full {{ $this->iqamaStatus['badgeClass'] }} text-sm font-semibold ring-1 ring-black/5">
                            ইকামা: {{ $this->iqamaStatus['label'] }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="border-t border-slate-100 px-6 sm:px-8 py-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ([
                        ['label' => 'অভিজ্ঞতা', 'value' => $worker->experience_years . ' বছর', 'icon' => '🛠️'],
                        ['label' => 'প্রত্যাশিত বেতন', 'value' => $worker->expected_salary_sar . ' SAR', 'icon' => '💰'],
                        ['label' => 'বয়স', 'value' => ($worker->date_of_birth?->age ?? '—') . ' বছর', 'icon' => '🎂'],
                        ['label' => 'আরবি দক্ষতা', 'value' => $worker->arabic_level, 'icon' => '🇸🇦'],
                        ['label' => 'ইংরেজি দক্ষতা', 'value' => $worker->english_level, 'icon' => '🇬🇧'],
                        ['label' => 'প্রোফাইল ভিউ', 'value' => $worker->view_count . ' বার', 'icon' => '👁️'],
                    ] as $stat)
                        <div class="rounded-2xl bg-slate-50 hover:bg-slate-100/80 transition-colors px-4 py-3.5 ring-1 ring-slate-100">
                            <p class="text-xs text-slate-400 mb-1 flex items-center gap-1">
                                <span>{{ $stat['icon'] }}</span> {{ $stat['label'] }}
                            </p>
                            <p class="text-sm font-bold text-slate-900 capitalize">{{ $stat['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- YouTube Video --}}
            @if ($this->youtubeEmbedUrl)
                <div class="border-t border-slate-100 px-6 sm:px-8 py-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-3" style="font-family: 'Noto Serif Bengali', serif;">কাজের ভিডিও</h2>
                    <div class="aspect-video rounded-2xl overflow-hidden bg-black shadow-inner ring-1 ring-slate-200">
                        <iframe src="{{ $this->youtubeEmbedUrl }}"
                                class="w-full h-full"
                                allowfullscreen
                                loading="lazy"></iframe>
                    </div>
                </div>
            @endif

            {{-- Contact Reveal Section --}}
            <div class="border-t border-slate-100 px-6 sm:px-8 py-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-900" style="font-family: 'Noto Serif Bengali', serif;">যোগাযোগ তথ্য</h2>
                    <span class="text-xs text-slate-400 bg-slate-50 px-2.5 py-1 rounded-full ring-1 ring-slate-100">প্রতি নম্বর ৫ SAR</span>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach ([
                        'primary'  => ['label' => 'প্রাইমারি ফোন', 'icon' => '📞', 'value' => $worker->phone_primary],
                        'whatsapp' => ['label' => 'WhatsApp নম্বর', 'icon' => '💬', 'value' => $worker->phone_whatsapp],
                        'saudi'    => ['label' => 'সৌদি নম্বর', 'icon' => '🇸🇦', 'value' => $worker->phone_saudi],
                    ] as $type => $item)
                        @if ($item['value'])
                            <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white px-4 py-3.5 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="text-xl shrink-0 w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center">{{ $item['icon'] }}</span>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-400 font-medium">{{ $item['label'] }}</p>
                                        @if (in_array($type, $revealedPhones))
                                            <p class="font-bold text-slate-900 truncate" dir="ltr">{{ $item['value'] }}</p>
                                        @else
                                            <p class="font-semibold text-slate-300 tracking-[0.2em]">••••••••••</p>
                                        @endif
                                    </div>
                                </div>

                                @unless (in_array($type, $revealedPhones))
                                    <button wire:click="revealPhone('{{ $type }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="revealPhone('{{ $type }}')"
                                            class="text-sm font-semibold text-white bg-emerald-800 hover:bg-emerald-900 active:scale-95 disabled:opacity-50 rounded-xl px-4 py-2 shrink-0 transition-all shadow-sm shadow-emerald-800/20">
                                        <span wire:loading.remove wire:target="revealPhone('{{ $type }}')">দেখুন (৫ SAR)</span>
                                        <span wire:loading wire:target="revealPhone('{{ $type }}')">অপেক্ষা করুন...</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-green-600 text-sm font-semibold shrink-0 bg-green-50 px-2.5 py-1 rounded-lg">
                                        ✓ দেখা হয়েছে
                                    </span>
                                @endunless
                            </div>
                        @endif
                    @endforeach
                </div>

                @guest
                    <div class="mt-4 flex items-center justify-between rounded-2xl bg-emerald-50 px-4 py-3.5 ring-1 ring-emerald-100">
                        <p class="text-sm text-emerald-800 font-medium">নম্বর দেখতে অ্যাকাউন্টে লগইন করুন।</p>
                        <a href="{{ route('login') }}" class="text-sm font-bold text-emerald-800 hover:text-emerald-900 shrink-0 transition-colors">
                            লগইন করুন →
                        </a>
                    </div>
                @endguest
            </div>
        </div>

        {{-- ============ SIMILAR WORKERS HORIZONTAL SLIDER ============ --}}
        @if (isset($similarWorkers) && $similarWorkers->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">👥</span>
                        <h2 class="text-xl font-bold text-slate-900" style="font-family: 'Noto Serif Bengali', serif;">
                            অনুরূপ কর্মী (Similar Workers)
                        </h2>
                    </div>
                    {{-- Scroll Tip Indicator --}}
                    <span class="text-xs text-slate-400 flex items-center gap-1 animate-pulse">
                        বামে স্ক্রোল করুন 
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </span>
                </div>

                {{-- Smooth Snap Scroller Container --}}
                <div class="flex overflow-x-auto gap-5 pb-5 snap-x snap-mandatory scrollbar-none scroll-smooth" style="-webkit-overflow-scrolling: touch;">
                    @foreach ($similarWorkers as $simWorker)
                        {{-- Mobile: 280px wide card | Desktop: perfectly balanced 3-column items --}}
                        <div class="snap-start shrink-0 w-[280px] sm:w-[calc(33.333%-14px)]">
                            <a href="{{ route('workers.show', $simWorker->uuid) }}"
                               class="group h-full bg-white rounded-3xl border border-slate-100 hover:border-[#0B4F3F]/30 hover:shadow-xl hover:shadow-slate-200/40 transition-all duration-300 p-4 flex flex-col justify-between ring-1 ring-slate-100 block">
                                
                                <div class="flex gap-3 items-start">
                                    {{-- Similar Worker Photo --}}
                                    @if ($simWorker->photo)
                                        <img src="{{ asset('storage/' . $simWorker->photo) }}"
                                             alt="{{ $simWorker->full_name_bn }}"
                                             class="w-14 h-14 rounded-2xl object-cover shrink-0 ring-2 ring-slate-50 group-hover:ring-[#0B4F3F]/20 transition-all">
                                    @else
                                        <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 text-[11px] shrink-0 font-medium">
                                            ছবি নেই
                                        </div>
                                    @endif

                                    {{-- Similar Worker Info --}}
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-slate-900 group-hover:text-[#0B4F3F] transition-colors truncate text-sm sm:text-base">
                                            {{ $simWorker->full_name_bn }}
                                        </h3>
                                        <p class="text-xs text-slate-400 truncate mt-0.5">
                                            {{ $simWorker->skillCategory?->name_bn ?? 'সাধারণ কর্মী' }}
                                        </p>
                                        
                                        {{-- Experience & Location Status Badges --}}
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <span class="text-[10px] px-2 py-0.5 rounded-md font-semibold bg-slate-50 text-slate-500 ring-1 ring-slate-100">
                                                {{ $simWorker->experience_years }} বছর
                                            </span>
                                            @if($simWorker->is_in_saudi)
                                                <span class="text-[10px] px-2 py-0.5 rounded-md font-semibold bg-green-50 text-green-700 ring-1 ring-green-100">
                                                    সৌদি
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Expected Salary & CTA Area --}}
                                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <div class="text-left">
                                        <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-medium">বেতন</span>
                                        <span class="text-sm font-bold text-[#0B4F3F]">{{ floatval($simWorker->expected_salary_sar) }} SAR</span>
                                    </div>
                                    <span class="text-xs font-bold text-[#C9974C] group-hover:translate-x-1 transition-transform flex items-center gap-0.5">
                                        প্রোফাইল →
                                    </span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Route B: Agent Nok Modal Area --}}
    @if ($showNokModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div wire:click="closeNokModal" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-[28px] shadow-2xl ring-1 ring-slate-100 w-full max-w-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $worker->full_name_bn }} কে Job Offer পাঠান
                    </h3>
                    <button wire:click="closeNokModal" class="text-slate-400 hover:text-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    @if ($nokError)
                        <div class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-4 py-3 text-sm flex items-center gap-2">
                            <span>⚠️</span> {{ $nokError }}
                        </div>
                    @endif

                    @if ($this->agentActiveJobPosts->isEmpty())
                        <div class="rounded-2xl bg-slate-50 ring-1 ring-slate-100 px-4 py-6 text-center text-sm text-slate-500">
                            আপনার কোনো Active Job Post নেই যেখানে vacancy খালি আছে।
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">Job Post নির্বাচন করুন</label>
                            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                @foreach ($this->agentActiveJobPosts as $job)
                                    <label class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3 cursor-pointer transition-colors
                                        {{ $selectedJobPostId === $job->id ? 'border-orange-400 bg-orange-50/60 ring-1 ring-orange-200' : 'border-slate-100 hover:bg-slate-50' }}
                                        {{ $job->nok_status ? 'opacity-60' : '' }}">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <input type="radio"
                                                   wire:model="selectedJobPostId"
                                                   value="{{ $job->id }}"
                                                   @disabled($job->nok_status)
                                                   class="accent-orange-500">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-900 truncate">{{ $job->job_title }}</p>
                                                <p class="text-xs text-slate-400">{{ $job->employer_city }} · {{ $job->salary_sar }} SAR</p>
                                            </div>
                                        </div>

                                        @if ($job->nok_status)
                                            <span @class([
                                                'text-xs font-semibold px-2.5 py-1 rounded-lg shrink-0',
                                                'bg-yellow-50 text-yellow-700' => $job->nok_status === 'pending',
                                                'bg-green-50 text-green-700' => $job->nok_status === 'accepted',
                                                'bg-red-50 text-red-700' => $job->nok_status === 'rejected',
                                                'bg-slate-100 text-slate-500' => $job->nok_status === 'expired',
                                            ])>
                                                @match($job->nok_status)
                                                    'pending' => 'পাঠানো হয়েছে',
                                                    'accepted' => 'গৃহীত',
                                                    'rejected' => 'প্রত্যাখ্যাত',
                                                    'expired' => 'মেয়াদোত্তীর্ণ',
                                                    default => '',
                                                @endmatch
                                            </span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-2">বার্তা (ঐচ্ছিক)</label>
                            <textarea wire:model="nokMessage"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Worker কে সংক্ষেপে কিছু বলুন..."
                                      class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400"></textarea>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button wire:click="closeNokModal"
                            class="text-sm font-semibold text-slate-500 hover:text-slate-800 px-4 py-2.5 transition-colors">
                        বাতিল
                    </button>
                    <button wire:click="sendNok"
                            wire:loading.attr="disabled"
                            wire:target="sendNok"
                            @disabled($this->agentActiveJobPosts->isEmpty())
                            class="text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 active:scale-95 disabled:opacity-50 rounded-xl px-5 py-2.5 transition-all shadow-sm shadow-orange-500/30">
                        <span wire:loading.remove wire:target="sendNok">পাঠান</span>
                        <span wire:loading wire:target="sendNok">পাঠানো হচ্ছে...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>