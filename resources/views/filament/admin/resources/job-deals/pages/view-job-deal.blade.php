<x-filament-panels::page>
    {{-- ── Deal Summary Header ─────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 mb-6">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary-500 via-primary-400 to-primary-300"></div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-filament::icon icon="heroicon-o-user" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Worker</p>
                    @if($record->worker)
                        <button
                            type="button"
                            wire:click="openWorkerProfileModal"
                            class="text-sm font-semibold text-gray-950 dark:text-white mt-0.5 hover:text-primary-600 dark:hover:text-primary-400 hover:underline text-left"
                        >
                            {{ $record->worker->full_name_bn ?? $record->worker->full_name_en ?? '—' }}
                        </button>
                        @if($record->worker->phone_whatsapp)
                            <p class="flex items-center gap-1 text-xs text-gray-400 mt-0.5">
                                <x-filament::icon icon="heroicon-o-chat-bubble-left-ellipsis" class="h-3 w-3" />
                                {{ $record->worker->phone_whatsapp }}
                            </p>
                        @endif
                    @else
                        <p class="text-sm font-semibold text-gray-950 dark:text-white mt-0.5">—</p>
                    @endif
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-filament::icon icon="heroicon-o-building-office" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Agent</p>
                    @if($record->agent?->agentProfile)
                        <button
                            type="button"
                            wire:click="openAgentProfileModal"
                            class="text-sm font-semibold text-gray-950 dark:text-white mt-0.5 hover:text-primary-600 dark:hover:text-primary-400 hover:underline text-left"
                        >
                            {{ $record->agent->name ?? '—' }}
                        </button>
                        @if($record->agent->agentProfile->whatsapp_number)
                            <p class="flex items-center gap-1 text-xs text-gray-400 mt-0.5">
                                <x-filament::icon icon="heroicon-o-chat-bubble-left-ellipsis" class="h-3 w-3" />
                                {{ $record->agent->agentProfile->whatsapp_number }}
                            </p>
                        @endif
                    @else
                        <p class="text-sm font-semibold text-gray-950 dark:text-white mt-0.5">
                            {{ $record->agent->name ?? '—' }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                    <x-filament::icon icon="heroicon-o-briefcase" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">জব</p>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white mt-0.5">
                        {{ $record->jobPost->job_title ?? '—' }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-50 dark:bg-success-500/10">
                    <x-filament::icon icon="heroicon-o-banknotes" class="h-5 w-5 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">মোট ফি</p>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white mt-0.5">
                        {{ number_format($record->agent_fee_sar, 2) }} SAR
                    </p>
                    <p class="text-xs text-gray-400">কমিশন: {{ number_format($record->chapai_commission_sar, 2) }} SAR</p>
                </div>
            </div>
        </div>

        @if($record->isDisputed())
            <div class="mt-5 flex items-center gap-2 rounded-lg bg-danger-50 dark:bg-danger-500/10 px-4 py-2.5">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-4 w-4 text-danger-600 dark:text-danger-400 shrink-0" />
                <p class="text-xs font-medium text-danger-700 dark:text-danger-400">
                    এই ডিলে একটি বিরোধ চলমান আছে — নিচে সংশ্লিষ্ট মাইলস্টোনে সমাধান করুন
                </p>
            </div>
        @endif
    </div>

    {{-- ── Progress Overview ───────────────────────────────────────────── --}}
    @php
        $totalMilestones = $this->milestones->count();
        $completedCount  = $this->milestones->where('status', 'admin_released')->count();
        $progressPercent = $totalMilestones > 0 ? round(($completedCount / $totalMilestones) * 100) : 0;
    @endphp

    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-950 dark:text-white">
                অগ্রগতি: {{ $completedCount }} / {{ $totalMilestones }} মাইলস্টোন সম্পন্ন
            </p>
            <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ $progressPercent }}%</p>
        </div>
        <div class="h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
            <div
                class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-400 transition-all duration-500"
                style="width: {{ $progressPercent }}%"
            ></div>
        </div>
    </div>

    {{-- ── Milestone Cards ─────────────────────────────────────────────── --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($this->milestones as $milestone)
            @php
                $borderColor = match($milestone->status) {
                    'admin_released' => 'border-l-success-500',
                    'disputed'       => 'border-l-danger-500',
                    'agent_confirmed'=> 'border-l-warning-500',
                    default          => 'border-l-gray-300 dark:border-l-gray-700',
                };
            @endphp

            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 border-l-4 {{ $borderColor }} bg-white dark:bg-gray-900 p-5 flex flex-col">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500">
                        <x-filament::icon icon="heroicon-o-flag" class="h-3.5 w-3.5" />
                        মাইলস্টোন {{ $milestone->milestone_number }}
                    </span>
                    <x-filament::badge :color="\App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::statusColor($milestone->status)" size="sm">
                        {{ \App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::statusLabel($milestone->status) }}
                    </x-filament::badge>
                </div>

                <p class="text-sm font-semibold text-gray-950 dark:text-white mb-1">
                    {{ $milestone->title }}
                </p>
                <p class="text-xs text-gray-500 mb-1">
                    {{ $milestone->percentage }}% &middot; মোট: {{ number_format($milestone->amount_sar, 2) }} SAR
                </p>
                <p class="text-xs text-gray-500 mb-4">
                    কমিশন: {{ number_format($milestone->commission_sar, 2) }} SAR &middot;
                    Agent পাবে: {{ number_format($milestone->agent_receives_sar, 2) }} SAR
                </p>

                <div class="space-y-1.5 text-xs text-gray-500 mb-4 rounded-lg bg-gray-50 dark:bg-gray-800/60 p-3">
                    <p class="flex items-center justify-between">
                        <span>Worker confirm</span>
                        <span class="font-medium {{ $milestone->worker_confirmed_at ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">
                            {{ $milestone->worker_confirmed_at?->format('d M, h:i A') ?? '—' }}
                        </span>
                    </p>
                    <p class="flex items-center justify-between">
                        <span>Agent confirm</span>
                        <span class="font-medium {{ $milestone->agent_confirmed_at ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">
                            {{ $milestone->agent_confirmed_at?->format('d M, h:i A') ?? '—' }}
                        </span>
                    </p>
                    <p class="flex items-center justify-between">
                        <span>Admin release</span>
                        <span class="font-medium {{ $milestone->admin_released_at ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">
                            {{ $milestone->admin_released_at?->format('d M, h:i A') ?? '—' }}
                        </span>
                    </p>
                </div>

                <div class="mt-auto">
                    @if($milestone->status === 'agent_confirmed' && $this->isSuperAdmin())
                        <x-filament::button
                            size="sm"
                            color="success"
                            icon="heroicon-o-check-circle"
                            class="w-full"
                            wire:click="openReleaseModal({{ $milestone->id }})"
                        >
                            পেমেন্ট রিলিজ করুন
                        </x-filament::button>
                    @elseif($milestone->status === 'agent_confirmed')
                        <p class="flex items-center gap-1.5 text-xs text-warning-600">
                            <x-filament::icon icon="heroicon-o-clock" class="h-3.5 w-3.5" />
                            Super Admin এর রিলিজের অপেক্ষায়
                        </p>
                    @elseif(in_array($milestone->status, ['pending', 'worker_confirmed']))
                        <p class="flex items-center gap-1.5 text-xs text-gray-500">
                            <x-filament::icon icon="heroicon-o-ellipsis-horizontal-circle" class="h-3.5 w-3.5" />
                            এখনো Worker/Agent কনফার্মেশন সম্পন্ন হয়নি
                        </p>
                    @elseif($milestone->status === 'admin_released')
                        <p class="flex items-center gap-1.5 text-xs text-success-600 mb-2">
                            <x-filament::icon icon="heroicon-o-check-badge" class="h-3.5 w-3.5" />
                            রিলিজ সম্পন্ন
                        </p>
                        @if($milestone->receipt_path)
                            <x-filament::button
                                size="sm"
                                color="gray"
                                icon="heroicon-o-document-arrow-down"
                                class="w-full"
                                tag="a"
                                href="{{ route('milestones.receipt.download', $milestone) }}"
                                target="_blank"
                            >
                                রশিদ ডাউনলোড
                            </x-filament::button>
                        @endif
                    @elseif($milestone->status === 'disputed')
                        <div class="rounded-lg bg-danger-50 dark:bg-danger-500/10 p-3 mb-3 space-y-1.5">
                            <p class="flex items-center gap-1.5 text-xs font-semibold text-danger-600">
                                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-3.5 w-3.5" />
                                বিরোধ তুলেছে: {{ \App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::disputeRaisedByLabel($milestone->dispute_raised_by) }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                {{ $milestone->dispute_reason }}
                            </p>
                            <p class="text-[11px] text-gray-400">
                                {{ $milestone->dispute_raised_at?->format('d M Y, h:i A') }}
                            </p>

                            @if($milestone->evidences->isNotEmpty())
                                <div class="pt-1.5 flex flex-wrap gap-1.5">
                                    @foreach($milestone->evidences as $evidence)
                                        
                                            href="{{ $this->evidenceDownloadUrl($evidence) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-md bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-2 py-1 text-[11px] text-gray-600 dark:text-gray-300 hover:border-primary-400"
                                        >
                                            <x-filament::icon icon="heroicon-o-paper-clip" class="h-3 w-3" />
                                            Evidence #{{ $loop->iteration }}
                                            <span class="text-gray-400">({{ strtoupper($evidence->file_type) }})</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if($this->isSuperAdmin())
                            <x-filament::button
                                size="sm"
                                color="danger"
                                icon="heroicon-o-scale"
                                class="w-full"
                                wire:click="openDisputeModal({{ $milestone->id }})"
                            >
                                বিরোধ সমাধান করুন
                            </x-filament::button>
                        @else
                            <p class="flex items-center gap-1.5 text-xs text-warning-600">
                                <x-filament::icon icon="heroicon-o-clock" class="h-3.5 w-3.5" />
                                Super Admin এর সমাধানের অপেক্ষায়
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Worker Profile Quick View Modal ─────────────────────────────── --}}
    @if($showWorkerModal && $record->worker)
        @php $worker = $record->worker; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4" wire:key="worker-profile-modal">
            <div class="w-full max-w-sm sm:max-w-2xl rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                        <x-filament::icon icon="heroicon-o-user" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h4 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $worker->full_name_bn ?? $worker->full_name_en ?? '—' }}
                        </h4>
                        @if($worker->full_name_bn && $worker->full_name_en)
                            <p class="text-xs text-gray-400">{{ $worker->full_name_en }}</p>
                        @endif
                        @if($worker->is_verified)
                            <span class="inline-flex items-center gap-1 text-xs text-success-600">
                                <x-filament::icon icon="heroicon-o-check-badge" class="h-3.5 w-3.5" />
                                ভেরিফাইড
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <x-filament::badge :color="\App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::workerStatusColor($worker->status)" size="sm">
                        {{ \App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::workerStatusLabel($worker->status) }}
                    </x-filament::badge>
                </div>

                {{-- Wallet Balance — শুধু worker_user_id link করা থাকলেই দেখাবে --}}
                @if($worker->workerUser)
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">ওয়ালেট ব্যালেন্স</p>
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="rounded-lg bg-success-50 dark:bg-success-500/10 p-2.5 text-center">
                            <p class="text-[10px] text-gray-500 mb-0.5">উত্তোলনযোগ্য</p>
                            <p class="text-xs font-semibold text-success-600">{{ number_format($worker->workerUser->available_balance, 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-warning-50 dark:bg-warning-500/10 p-2.5 text-center">
                            <p class="text-[10px] text-gray-500 mb-0.5">হোল্ড</p>
                            <p class="text-xs font-semibold text-warning-600">{{ number_format($worker->workerUser->held_balance, 2) }}</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2.5 text-center">
                            <p class="text-[10px] text-gray-500 mb-0.5">মোট</p>
                            <p class="text-xs font-semibold text-gray-950 dark:text-white">{{ number_format($worker->workerUser->totalBalance(), 2) }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 dark:bg-gray-800/60 px-3 py-2 mb-4">
                        <x-filament::icon icon="heroicon-o-information-circle" class="h-3.5 w-3.5 text-gray-400 shrink-0" />
                        <p class="text-xs text-gray-500">এই ওয়ার্কার এখনো নিজের অ্যাকাউন্ট claim করেনি — ওয়ালেট নেই</p>
                    </div>
                @endif

                {{-- দক্ষতা প্রোফাইল --}}
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">দক্ষতা প্রোফাইল</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2.5 mb-4">
                    @if($worker->skillCategory)
                        <div>
                            <p class="text-[11px] text-gray-500">ক্যাটাগরি</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->skillCategory->name }}</p>
                        </div>
                    @endif
                    @if($worker->experience_years !== null)
                        <div>
                            <p class="text-[11px] text-gray-500">মোট অভিজ্ঞতা</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->experience_years }} বছর</p>
                        </div>
                    @endif
                    @if($worker->experience_saudi_years !== null)
                        <div>
                            <p class="text-[11px] text-gray-500">সৌদি অভিজ্ঞতা</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->experience_saudi_years }} বছর</p>
                        </div>
                    @endif
                    @if($worker->education_level)
                        <div>
                            <p class="text-[11px] text-gray-500">শিক্ষাগত যোগ্যতা</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ \App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::educationLevelLabel($worker->education_level) }}</p>
                        </div>
                    @endif
                    @if($worker->arabic_level)
                        <div>
                            <p class="text-[11px] text-gray-500">আরবি ভাষা</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ \App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::languageLevelLabel($worker->arabic_level) }}</p>
                        </div>
                    @endif
                    @if($worker->english_level)
                        <div>
                            <p class="text-[11px] text-gray-500">ইংরেজি ভাষা</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ \App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::languageLevelLabel($worker->english_level) }}</p>
                        </div>
                    @endif
                </div>

                {{-- বর্তমান অবস্থা ও প্রাপ্যতা --}}
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">বর্তমান অবস্থা ও প্রাপ্যতা</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2.5 mb-4">
                    <div>
                        <p class="text-[11px] text-gray-500">বর্তমান অবস্থান</p>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">
                            {{ $worker->is_in_saudi ? ($worker->present_location_city ?? 'সৌদি আরবে আছে') : 'বাংলাদেশে আছে' }}
                        </p>
                    </div>
                    @if($worker->is_in_saudi && $worker->visa_status)
                        <div>
                            <p class="text-[11px] text-gray-500">ভিসা স্ট্যাটাস</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ \App\Filament\Admin\Resources\JobDeals\Pages\ViewJobDeal::visaStatusLabel($worker->visa_status) }}</p>
                        </div>
                    @endif
                    @if($worker->is_in_saudi)
                        <div>
                            <p class="text-[11px] text-gray-500">ট্রান্সফার সম্ভব</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->transfer_possible ? 'হ্যাঁ' : 'না' }}</p>
                        </div>
                    @endif
                    @if($worker->available_from)
                        <div>
                            <p class="text-[11px] text-gray-500">কাজ শুরু করতে পারবে</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->available_from->format('d M Y') }}</p>
                        </div>
                    @endif
                    @if($worker->expected_salary_sar)
                        <div>
                            <p class="text-[11px] text-gray-500">প্রত্যাশিত বেতন</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ number_format($worker->expected_salary_sar, 2) }} SAR</p>
                        </div>
                    @endif
                </div>

                {{-- কমপ্লায়েন্স --}}
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">কমপ্লায়েন্স</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2.5 mb-5">
                    @if($worker->date_of_birth)
                        <div>
                            <p class="text-[11px] text-gray-500">বয়স</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->date_of_birth->age }} বছর</p>
                        </div>
                    @endif
                    @if($worker->iqama_expiry)
                        <div>
                            <p class="text-[11px] text-gray-500">ইকামা মেয়াদ</p>
                            <p class="text-sm font-medium {{ $worker->isIqamaExpiringSoon() ? 'text-danger-600' : 'text-gray-950 dark:text-white' }}">
                                {{ $worker->iqama_expiry->format('d M Y') }}
                                @if($worker->isIqamaExpiringSoon())
                                    <span class="text-[10px]">(শীঘ্রই শেষ)</span>
                                @endif
                            </p>
                        </div>
                    @endif
                    <div>
                        <p class="text-[11px] text-gray-500">ড্রাইভিং লাইসেন্স</p>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->driving_license ? 'আছে' : 'নেই' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500">মেডিকেল ফিট</p>
                        <p class="text-sm font-medium {{ $worker->medical_fit ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $worker->medical_fit ? 'হ্যাঁ' : 'না' }}
                        </p>
                    </div>
                    @if($worker->phone_whatsapp)
                        <div>
                            <p class="text-[11px] text-gray-500">WhatsApp</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $worker->phone_whatsapp }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="closeWorkerProfileModal">
                        বন্ধ করুন
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-arrow-top-right-on-square"
                        tag="a"
                        href="{{ $worker->public_url }}"
                        target="_blank"
                    >
                        ফুল প্রোফাইল দেখুন
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Agent Profile Quick View Modal ──────────────────────────────── --}}
    @if($showAgentModal && $record->agent?->agentProfile)
        @php
            $agentProfile = $record->agent->agentProfile;
            $agentUser    = $record->agent;

            $companyTypeLabel = match($agentProfile->company_type) {
                'individual'          => 'ব্যক্তিগত',
                'registered_company'  => 'নিবন্ধিত কোম্পানি',
                'recruitment_agency'  => 'রিক্রুটমেন্ট এজেন্সি',
                default                => null,
            };
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4" wire:key="agent-profile-modal">
            <div class="w-full max-w-sm sm:max-w-2xl rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                        <x-filament::icon icon="heroicon-o-building-office" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h4 class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $agentProfile->company_name ?? $agentUser->name }}
                        </h4>
                        @if($agentProfile->company_name)
                            <p class="text-xs text-gray-400">{{ $agentUser->name }}</p>
                        @endif
                        @if($agentProfile->is_verified)
                            <span class="inline-flex items-center gap-1 text-xs text-success-600">
                                <x-filament::icon icon="heroicon-o-check-badge" class="h-3.5 w-3.5" />
                                ভেরিফাইড এজেন্ট
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                <x-filament::icon icon="heroicon-o-clock" class="h-3.5 w-3.5" />
                                ভেরিফিকেশন অপেক্ষমান
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Wallet Balance --}}
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <div class="rounded-lg bg-success-50 dark:bg-success-500/10 p-2.5 text-center">
                        <p class="text-[10px] text-gray-500 mb-0.5">উত্তোলনযোগ্য</p>
                        <p class="text-xs font-semibold text-success-600">{{ number_format($agentUser->available_balance, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-warning-50 dark:bg-warning-500/10 p-2.5 text-center">
                        <p class="text-[10px] text-gray-500 mb-0.5">হোল্ড</p>
                        <p class="text-xs font-semibold text-warning-600">{{ number_format($agentUser->held_balance, 2) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2.5 text-center">
                        <p class="text-[10px] text-gray-500 mb-0.5">মোট</p>
                        <p class="text-xs font-semibold text-gray-950 dark:text-white">{{ number_format($agentUser->totalBalance(), 2) }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2.5 mb-5">
                    @if($companyTypeLabel)
                        <div>
                            <p class="text-[11px] text-gray-500">কোম্পানির ধরন</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $companyTypeLabel }}</p>
                        </div>
                    @endif
                    @if($agentProfile->city || $agentProfile->country)
                        <div>
                            <p class="text-[11px] text-gray-500">অবস্থান</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">
                                {{ collect([$agentProfile->city, $agentProfile->country])->filter()->implode(', ') }}
                            </p>
                        </div>
                    @endif
                    @if($agentProfile->years_in_business)
                        <div>
                            <p class="text-[11px] text-gray-500">ব্যবসার বয়স</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $agentProfile->years_in_business }} বছর</p>
                        </div>
                    @endif
                    @if($agentProfile->whatsapp_number)
                        <div>
                            <p class="text-[11px] text-gray-500">WhatsApp</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $agentProfile->whatsapp_number }}</p>
                        </div>
                    @endif
                    @if($agentProfile->phone_office)
                        <div>
                            <p class="text-[11px] text-gray-500">অফিস ফোন</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $agentProfile->phone_office }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-[11px] text-gray-500">মোট ডিল</p>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $agentProfile->total_deals }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500">সফল ডিল</p>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $agentProfile->successful_deals }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500">মোট ওয়ার্কার প্লেসমেন্ট</p>
                        <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $agentProfile->total_workers_placed }}</p>
                    </div>
                    @if($agentProfile->last_deal_at)
                        <div>
                            <p class="text-[11px] text-gray-500">শেষ ডিল</p>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $agentProfile->last_deal_at->format('d M Y') }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="closeAgentProfileModal">
                        বন্ধ করুন
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-arrow-top-right-on-square"
                        tag="a"
                        href="{{ $agentProfile->public_url }}"
                        target="_blank"
                    >
                        ফুল প্রোফাইল দেখুন
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Release Confirmation Modal ──────────────────────────────────── --}}
    @if($releasingMilestoneId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4" wire:key="release-modal">
            <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10 mb-4">
                    <x-filament::icon icon="heroicon-o-banknotes" class="h-6 w-6 text-success-600 dark:text-success-400" />
                </div>

                <h4 class="text-base font-semibold text-gray-950 dark:text-white mb-2">
                    পেমেন্ট রিলিজ নিশ্চিত করুন
                </h4>
                <p class="text-sm text-gray-500 mb-5">
                    এই মাইলস্টোনের পেমেন্ট Agent এর ওয়ালেটে পাঠানো হবে এবং Worker এর held balance থেকে
                    চূড়ান্তভাবে কেটে নেওয়া হবে। এই কাজটি ফেরত নেওয়া যাবে না।
                </p>
                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="closeReleaseModal">
                        বাতিল
                    </x-filament::button>
                    <x-filament::button color="success" icon="heroicon-o-check" wire:click="releaseMilestone">
                        হ্যাঁ, রিলিজ করুন
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Dispute Resolution Modal ────────────────────────────────────── --}}
    @if($resolvingMilestoneId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4" wire:key="dispute-modal">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-2xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-danger-50 dark:bg-danger-500/10 mb-4">
                    <x-filament::icon icon="heroicon-o-scale" class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                </div>

                <h4 class="text-base font-semibold text-gray-950 dark:text-white mb-4">
                    বিরোধ সমাধান করুন
                </h4>

                <div class="space-y-3 mb-4">
                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3 cursor-pointer has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-500/10">
                        <input type="radio" wire:model.live="resolutionAction" value="full_refund" class="text-primary-600">
                        <span class="text-sm">
                            <span class="font-medium text-gray-950 dark:text-white">সম্পূর্ণ ফেরত</span>
                            <span class="block text-xs text-gray-500">Worker এর held balance পুরোপুরি available এ ফেরত যাবে</span>
                        </span>
                    </label>

                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3 cursor-pointer has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-500/10">
                        <input type="radio" wire:model.live="resolutionAction" value="full_release" class="text-primary-600">
                        <span class="text-sm">
                            <span class="font-medium text-gray-950 dark:text-white">সম্পূর্ণ রিলিজ</span>
                            <span class="block text-xs text-gray-500">স্বাভাবিক রিলিজের মতো Agent সম্পূর্ণ অংশ পাবে</span>
                        </span>
                    </label>

                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3 cursor-pointer has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-500/10">
                        <input type="radio" wire:model.live="resolutionAction" value="partial" class="text-primary-600">
                        <span class="text-sm">
                            <span class="font-medium text-gray-950 dark:text-white">আংশিক সমাধান</span>
                            <span class="block text-xs text-gray-500">Worker ও Agent এর মধ্যে ভাগ করে দিন</span>
                        </span>
                    </label>

                    @if($resolutionAction === 'partial')
                        <div class="pl-3">
                            <label class="text-xs text-gray-500 mb-1 block">Worker কে ফেরত দেওয়ার শতাংশ (%)</label>
                            <input
                                type="number" min="0" max="100" step="1"
                                wire:model="partialWorkerPct"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm"
                            >
                        </div>
                    @endif

                    <label class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 p-3 cursor-pointer has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-500/10">
                        <input type="radio" wire:model.live="resolutionAction" value="extend" class="text-primary-600">
                        <span class="text-sm">
                            <span class="font-medium text-gray-950 dark:text-white">সময়সীমা বর্ধিত করুন</span>
                            <span class="block text-xs text-gray-500">টাকা movement হবে না, শুধু নতুন করে কনফার্মের সুযোগ দেওয়া হবে</span>
                        </span>
                    </label>
                </div>

                <div class="mb-5">
                    <label class="text-xs text-gray-500 mb-1 block">অ্যাডমিন নোট (আবশ্যক)</label>
                    <textarea
                        wire:model="resolutionNotes"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm"
                        placeholder="সিদ্ধান্তের কারণ লিখুন..."
                    ></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" wire:click="closeDisputeModal">
                        বাতিল
                    </x-filament::button>
                    <x-filament::button color="danger" icon="heroicon-o-check" wire:click="submitDisputeResolution">
                        সমাধান নিশ্চিত করুন
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>