<div>
    <div class="max-w-5xl mx-auto px-4 py-8">

        {{-- BREADCRUMB --}}
        <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
            <a href="{{ route('jobs.index') }}" wire:navigate class="transition"
               onmouseover="this.style.color='#0B4F3F'" onmouseout="this.style.color=''">{{ __('messages.job_show.breadcrumb_jobs') }}</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            <span class="text-gray-600 font-medium truncate">{{ $job->job_title }}</span>
        </div>

        {{-- FLASH MESSAGES --}}
        @if (session('job_success'))
            <div class="mb-5 bg-green-50 border border-green-100 text-green-700 text-sm rounded-xl px-4 py-3">
                {{ session('job_success') }}
            </div>
        @endif
        @if (session('job_error'))
            <div class="mb-5 bg-red-50 border border-red-100 text-red-700 text-sm rounded-xl px-4 py-3">
                {{ session('job_error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- MAIN INFO --}}
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900" style="font-family: 'Noto Serif Bengali', serif;">{{ $job->job_title }}</h1>
                            @if ($job->job_title_ar)
                                <p class="text-sm text-gray-400 mt-1" dir="rtl">{{ $job->job_title_ar }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-xs font-medium px-3 py-1.5 rounded-full whitespace-nowrap" style="background-color:rgba(11,79,63,0.07); color:#0B4F3F;">
                            {{ $job->skillCategory?->name_bn }}
                        </span>
                    </div>

                    <p class="flex items-center gap-1.5 text-sm text-gray-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        {{ $job->employer_name }} • {{ $job->employer_city }}, {{ $job->employer_country }}
                        <span class="text-xs text-gray-300">({{ ucfirst($job->employer_type) }})</span>
                    </p>

                    <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                        <x-job-vacancy-counter :job="$job" />
                        <x-job-expiry-counter :job="$job" />
                    </div>
                </div>

                {{-- TERMS --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="font-semibold text-gray-800 text-sm mb-4">{{ __('messages.job_show.terms_title') }}</h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-5">
                        <div>
                            <p class="text-[11px] text-gray-400">{{ __('messages.job_show.salary') }}</p>
                            <p class="font-semibold text-gray-900">{{ number_format($job->salary_sar, 2) }} SAR</p>
                        </div>
                        @if ($job->contract_months)
                            <div>
                                <p class="text-[11px] text-gray-400">{{ __('messages.job_show.contract_duration') }}</p>
                                <p class="font-semibold text-gray-900">{{ $job->contract_months }} {{ __('messages.job_show.months') }}</p>
                            </div>
                        @endif
                        @if ($job->working_hours)
                            <div>
                                <p class="text-[11px] text-gray-400">{{ __('messages.job_show.working_hours') }}</p>
                                <p class="font-semibold text-gray-900">{{ $job->working_hours }} {{ __('messages.job_show.hours_per_day') }}</p>
                            </div>
                        @endif
                        @if ($job->weekly_off)
                            <div>
                                <p class="text-[11px] text-gray-400">{{ __('messages.job_show.weekly_off') }}</p>
                                <p class="font-semibold text-gray-900">{{ $job->weekly_off }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if ($job->accommodation)
                            <span class="text-xs font-medium bg-blue-50 text-blue-600 px-3 py-1.5 rounded-full">{{ __('messages.job_show.accommodation') }}</span>
                        @endif
                        @if ($job->food_included)
                            <span class="text-xs font-medium bg-green-50 text-green-600 px-3 py-1.5 rounded-full">{{ __('messages.job_show.food') }}</span>
                        @endif
                        @if ($job->transport_provided)
                            <span class="text-xs font-medium bg-purple-50 text-purple-600 px-3 py-1.5 rounded-full">{{ __('messages.job_show.transport') }}</span>
                        @endif
                        @if ($job->overtime_available)
                            <span class="text-xs font-medium bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-full">{{ __('messages.job_show.overtime') }}</span>
                        @endif
                    </div>
                </div>

                @if ($job->description)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="font-semibold text-gray-800 text-sm mb-3">{{ __('messages.job_show.description_title') }}</h2>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $job->description }}</p>
                    </div>
                @endif

                @if ($job->requirements)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="font-semibold text-gray-800 text-sm mb-3">{{ __('messages.job_show.requirements_title') }}</h2>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $job->requirements }}</p>
                    </div>
                @endif
            </div>

            {{-- SIDEBAR: FEE REVEAL + INTEREST --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sticky top-4 space-y-6">

                    {{-- AGENT FEE --}}
                    <div>
                        <p class="text-[11px] font-semibold text-gray-400 tracking-wide uppercase mb-2.5">{{ __('messages.job_show.agent_fee') }}</p>

                        @if ($hasRevealedFee)
                            <div class="flex items-baseline gap-1.5">
                                <p class="text-3xl font-bold text-gray-900">{{ number_format((float) $revealedFeeAmount, 2) }}</p>
                                <span class="text-sm font-medium text-gray-400">SAR</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2.5 mb-3 bg-gray-50 rounded-xl px-4 py-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium text-gray-400">{{ __('messages.job_show.fee_not_revealed') }}</span>
                            </div>

                            <button
                                wire:click="revealFee"
                                wire:loading.attr="disabled"
                                wire:target="revealFee"
                                class="w-full flex items-center justify-center gap-2 text-sm font-medium text-white rounded-xl px-4 py-2.5 active:scale-[0.98] transition-all duration-150 disabled:opacity-60"
                                style="background-color:#0B4F3F;"
                                onmouseover="this.style.backgroundColor='#0e6350'"
                                onmouseout="this.style.backgroundColor='#0B4F3F'"
                            >
                                <svg wire:loading.remove wire:target="revealFee" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                <span wire:loading.remove wire:target="revealFee">{{ number_format($job->fee_reveal_cost, 2) }} SAR {{ __('messages.job_show.view_fee_for') }}</span>
                                <span wire:loading wire:target="revealFee">{{ __('messages.job_show.revealing') }}</span>
                            </button>
                            <p class="text-[11px] text-gray-300 mt-2 text-center">{{ __('messages.job_show.wallet_deduct_note', ['amount' => number_format($job->fee_reveal_cost, 2)]) }}</p>
                        @endif
                    </div>
                    {{-- INTEREST --}}
                    <div class="pt-5 border-t border-gray-100">

                        @if ($hasExpressedInterest)
                            {{-- অবস্থা ১: ইতিমধ্যে আগ্রহ প্রকাশ করা হয়েছে --}}
                            <div class="flex items-center gap-2 text-green-600 bg-green-50 rounded-xl px-4 py-3 text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ __('messages.job_show.interest_expressed') }}
                            </div>

                        @elseif (! auth()->check())
                            {{-- অবস্থা ২: Guest — বাটন দেখাও, ক্লিকে login redirect --}}
                            <p class="text-[11px] font-semibold text-gray-400 tracking-wide uppercase mb-3">{{ __('messages.job_show.express_interest') }}</p>
                            <a href="{{ route('login') }}"
                               class="block text-center text-sm font-semibold text-white rounded-xl py-3 active:scale-[0.98] transition-all duration-150"
                               style="background-color:#0B4F3F;"
                               onmouseover="this.style.backgroundColor='#0e6350'"
                               onmouseout="this.style.backgroundColor='#0B4F3F'">
                                {{ __('messages.job_show.login_to_express') }}
                            </a>

                        @elseif ($isAgent)
                            {{-- অবস্থা ৩: Agent — Step 5.2 CV-select modal (Way 3) --}}
                            <p class="text-[11px] font-semibold text-gray-400 tracking-wide uppercase mb-3">{{ __('messages.job_show.submit_worker') }}</p>

                            @if ($isOwnJobPost)
                                <div class="bg-gray-50 rounded-xl px-4 py-3.5 text-center">
                                    <p class="text-xs text-gray-400">
                                        {!! __('messages.job_show.own_job_post_hint', ['panel' => '<span class="font-medium text-gray-500">' . __('messages.job_show.my_job_posts_panel') . '</span>']) !!}
                                    </p>
                                </div>
                            @elseif (! $isVerifiedAgent)
                                <div class="bg-red-50 rounded-xl px-4 py-3.5 text-center">
                                    <p class="text-xs text-red-500">
                                        {{ __('messages.job_show.not_verified_agent') }}
                                    </p>
                                </div>
                            @elseif (! $hasRevealedFee)
                                <button disabled
                                    class="w-full text-sm font-medium bg-gray-100 text-gray-400 rounded-xl py-3 cursor-not-allowed">
                                    {{ __('messages.job_show.submit_worker') }}
                                </button>
                                <p class="text-[11px] text-gray-300 mt-2 text-center">{{ __('messages.job_show.view_fee_first') }}</p>
                            @else
                                <button
                                    wire:click="openWorkerModal"
                                    class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-white rounded-xl py-3 active:scale-[0.98] transition-all duration-150"
                                    style="background-color:#0B4F3F;"
                                    onmouseover="this.style.backgroundColor='#0e6350'"
                                    onmouseout="this.style.backgroundColor='#0B4F3F'"
                                >
                                    {{ __('messages.job_show.submit_worker') }}
                                </button>
                            @endif

                        @elseif (in_array(auth()->user()->role, ['admin', 'super_admin', 'staff'], true))
                            {{-- অবস্থা ৪: Admin/Staff — শুধু info hint --}}
                            <div class="bg-gray-50 rounded-xl px-4 py-3.5 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300 mx-auto mb-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                                </svg>
                                <p class="text-xs text-gray-400">{{ __('messages.job_show.agent_staff_only_note') }}</p>
                            </div>

                        @elseif (! $workerCvId || ! $workerCvEligible)
                            {{-- অবস্থা ৫: Worker কিন্তু CV Active না — disabled বাটন + reason --}}
                            <p class="text-[11px] font-semibold text-gray-400 tracking-wide uppercase mb-3">{{ __('messages.job_show.express_interest') }}</p>
                            <button disabled
                                class="w-full text-sm font-medium bg-gray-100 text-gray-400 rounded-xl py-3 cursor-not-allowed">
                                {{ __('messages.job_show.express_interest') }}
                            </button>
                            <p class="text-[11px] text-gray-400 mt-2 text-center">{{ __('messages.job_show.cv_not_active') }}</p>

                        @else
                            {{-- অবস্থা ৬: Worker, eligible — পুরো ফর্ম --}}
                            <p class="text-[11px] font-semibold text-gray-400 tracking-wide uppercase mb-3">{{ __('messages.job_show.express_interest') }}</p>
                            <div class="space-y-3">
                                <textarea
                                    wire:model="interestNote"
                                    rows="3"
                                    placeholder="{{ __('messages.job_show.optional_note_placeholder') }}"
                                    class="w-full text-sm rounded-lg border-gray-200 placeholder:text-gray-300"
                                    onfocus="this.style.borderColor='#0B4F3F'" onblur="this.style.borderColor='#e5e7eb'"
                                ></textarea>
                                <button
                                    wire:click="submitInterest"
                                    wire:loading.attr="disabled"
                                    wire:target="submitInterest"
                                    class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-white rounded-xl py-3 active:scale-[0.98] transition-all duration-150 disabled:opacity-60"
                                    style="background-color:#0B4F3F;"
                                    onmouseover="this.style.backgroundColor='#0e6350'"
                                    onmouseout="this.style.backgroundColor='#0B4F3F'"
                                >
                                    <span wire:loading.remove wire:target="submitInterest">{{ __('messages.job_show.express_interest') }}</span>
                                    <span wire:loading wire:target="submitInterest">{{ __('messages.job_show.submitting') }}</span>
                                </button>
                                @if (! $hasRevealedFee)
                                    <p class="text-[11px] text-gray-300 text-center">{{ __('messages.job_show.view_fee_before_interest') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── AGENT — Worker CV Select Modal (Step 5.2 — Way 3) ─── --}}
    @if ($isAgent && $showWorkerModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" wire:key="worker-select-modal">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('messages.job_show.select_worker_cv') }}</h3>
                    <button wire:click="closeWorkerModal" type="button" class="text-gray-300 hover:text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto">
                    <input
                        type="text"
                        wire:model.live.debounce.400ms="workerSearch"
                        placeholder="{{ __('messages.job_show.search_worker_placeholder') }}"
                        class="w-full text-sm rounded-lg border-gray-200 placeholder:text-gray-300"
                        onfocus="this.style.borderColor='#0B4F3F'" onblur="this.style.borderColor='#e5e7eb'"
                    >

                    <div class="space-y-2">
                        @forelse ($this->workerSearchResults as $worker)
                            <button
                                type="button"
                                @if(!$worker->already_applied) wire:click="selectWorker({{ $worker->id }}, '{{ addslashes($worker->full_name_bn ?? $worker->full_name_en) }}')" @endif
                                @disabled($worker->already_applied)
                                class="w-full text-left flex items-center justify-between px-3.5 py-2.5 rounded-xl border transition
                                    {{ $selectedAgentWorkerId === $worker->id ? '' : 'border-gray-100' }}
                                    {{ $worker->already_applied ? 'opacity-50 cursor-not-allowed' : '' }}"
                                @if($selectedAgentWorkerId === $worker->id)
                                    style="border-color:#C9974C; background-color:rgba(201,151,76,0.08);"
                                @endif
                                @unless($worker->already_applied)
                                    onmouseover="if(!this.disabled){this.style.borderColor='#C9974C'}"
                                    onmouseout="if(!this.disabled){this.style.borderColor='{{ $selectedAgentWorkerId === $worker->id ? '#C9974C' : '#f3f4f6' }}'}"
                                @endunless
                            >
                                <span>
                                    <span class="text-sm font-medium text-gray-800">{{ $worker->full_name_bn ?? $worker->full_name_en }}</span>
                                    <span class="text-xs text-gray-400 block">{{ $worker->skillCategory?->name_bn }}</span>
                                </span>
                                @if ($worker->already_applied)
                                    <span class="text-[11px] text-red-500 shrink-0">{{ __('messages.job_show.already_applied') }}</span>
                                @endif
                            </button>
                        @empty
                            @if (mb_strlen(trim($workerSearch)) >= 2)
                                <p class="text-sm text-gray-300 text-center py-2">{{ __('messages.job_show.no_worker_found') }}</p>
                            @endif
                        @endforelse
                    </div>

                    @if ($selectedAgentWorkerId)
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs text-gray-500 mb-2">
                                {{ __('messages.job_show.selected') }}: <span class="font-medium text-gray-800">{{ $selectedAgentWorkerName }}</span>
                            </p>
                            <textarea
                                wire:model="agentInterestNote"
                                rows="2"
                                placeholder="{{ __('messages.job_show.optional_note') }}"
                                class="w-full text-sm rounded-lg border-gray-200 placeholder:text-gray-300"
                                onfocus="this.style.borderColor='#0B4F3F'" onblur="this.style.borderColor='#e5e7eb'"
                            ></textarea>
                        </div>
                    @endif
                </div>

                <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2">
                    <button wire:click="closeWorkerModal" type="button" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">
                        {{ __('messages.job_show.cancel') }}
                    </button>
                    <button
                        wire:click="submitAgentInterest"
                        wire:loading.attr="disabled"
                        wire:target="submitAgentInterest"
                        type="button"
                        @disabled(!$selectedAgentWorkerId)
                        class="px-4 py-2 text-white text-sm font-semibold rounded-xl active:scale-[0.98] transition-all duration-150 disabled:opacity-50"
                        style="background-color:#0B4F3F;"
                        onmouseover="this.style.backgroundColor='#0e6350'"
                        onmouseout="this.style.backgroundColor='#0B4F3F'"
                    >
                        <span wire:loading.remove wire:target="submitAgentInterest">{{ __('messages.job_show.submit_interest') }}</span>
                        <span wire:loading wire:target="submitAgentInterest">{{ __('messages.job_show.submitting') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>