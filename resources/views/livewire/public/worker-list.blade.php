<div class="max-w-6xl mx-auto px-4 py-8" style="font-family: 'Hind Siliguri', sans-serif;">
    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-6" style="font-family: 'Noto Serif Bengali', serif;">
        কর্মীদের তালিকা
    </h1>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-6 flex flex-wrap gap-3 items-center">
        <select wire:model.live="skillCategoryId"
                class="rounded-lg border-gray-200 text-sm focus:ring-2 focus:border-transparent"
                style="--tw-ring-color:#0B4F3F;">
            <option value="">সব পেশা</option>
            @foreach ($skillCategories as $category)
                <option value="{{ $category->id }}">
                    {{ $category->name_bn }} ({{ str_replace(['0','1','2','3','4','5','6','7','8','9'], ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], $category->workers_count) }})
                </option>
            @endforeach
        </select>

        <select wire:model.live="visaStatus"
                class="rounded-lg border-gray-200 text-sm focus:ring-2 focus:border-transparent"
                style="--tw-ring-color:#0B4F3F;">
            <option value="">সব ভিসা স্ট্যাটাস</option>
            <option value="visit">ভিজিট</option>
            <option value="iqama">ইকামা</option>
            <option value="free_exit">ফ্রি এক্সিট</option>
            <option value="final_exit">ফাইনাল এক্সিট</option>
            <option value="new_visa">নতুন ভিসা</option>
            <option value="not_in_saudi">সৌদিতে নেই</option>
        </select>

        <select wire:model.live="isInSaudi"
                class="rounded-lg border-gray-200 text-sm focus:ring-2 focus:border-transparent"
                style="--tw-ring-color:#0B4F3F;">
            <option value="">সব অবস্থান</option>
            <option value="1">সৌদিতে আছেন</option>
            <option value="0">বাংলাদেশে আছেন</option>
        </select>

        @if ($skillCategoryId || $visaStatus || ! is_null($isInSaudi))
            <button wire:click="resetFilters"
                    class="text-sm font-medium hover:underline ml-auto"
                    style="color:#0B4F3F;">
                ফিল্টার রিসেট করুন ✕
            </button>
        @endif
    </div>

    {{-- Grid --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($workers as $worker)
            <a href="{{ route('workers.show', $worker->uuid) }}"
               class="group bg-white rounded-2xl border border-gray-100 transition p-4 flex gap-4 hover:shadow-md"
               onmouseover="this.style.borderColor='rgba(11,79,63,0.2)'"
               onmouseout="this.style.borderColor='#f3f4f6'">
                @if ($worker->photo)
                    <img src="{{ asset('storage/' . $worker->photo) }}"
                         alt="{{ $worker->full_name_bn }}"
                         class="w-20 h-20 rounded-xl object-cover shrink-0 group-hover:opacity-90 transition">
                @else
                    <div class="w-20 h-20 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 text-xs shrink-0">
                        ছবি নেই
                    </div>
                @endif

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-gray-900 truncate">{{ $worker->full_name_bn }}</p>
                        {{-- Featured logic matching custom backend date check --}}
                        @if ($worker->is_featured && (is_null($worker->featured_until) || $worker->featured_until >= now()->toDateString()))
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium shrink-0"
                                  style="background-color:rgba(201,151,76,0.15); color:#a4762f;">
                                ⭐ ফিচারড
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 truncate">{{ $worker->skillCategory?->name_bn }}</p>
                    <p class="text-sm text-gray-400 mt-1 flex items-center gap-1">
                        <span class="inline-block w-1.5 h-1.5 rounded-full shrink-0"
                              style="background-color: {{ $worker->is_in_saudi ? '#16a34a' : '#9ca3af' }};"></span>
                        {{ $worker->is_in_saudi ? 'সৌদিতে আছেন' : 'বাংলাদেশে আছেন' }}
                    </p>
                    <p class="text-sm font-medium mt-1.5" style="color:#0B4F3F;">
                        {{ floatval($worker->expected_salary_sar) }} SAR
                    </p>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-16">
                <p class="text-gray-400">কোনো কর্মী পাওয়া যায়নি।</p>
                @if ($skillCategoryId || $visaStatus || ! is_null($isInSaudi))
                    <button wire:click="resetFilters" class="mt-2 text-sm font-medium hover:underline" style="color:#0B4F3F;">
                        ফিল্টার রিসেট করে আবার দেখুন
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $workers->links() }}
    </div>
</div>