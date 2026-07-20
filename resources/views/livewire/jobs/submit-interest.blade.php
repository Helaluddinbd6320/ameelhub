<div>
    @if ($alreadyApplied)
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
            ✅ আপনি ইতিমধ্যে এই জবে আবেদন করেছেন। এজেন্ট শীঘ্রই যোগাযোগ করবে।
        </div>
    @else
        @if ($errorMessage)
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm mb-3">
                {{ $errorMessage }}
            </div>
        @endif

        @if (! $showNoteForm)
            <button
                wire:click="openNoteForm"
                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-6 rounded-lg transition"
            >
                এই জবে আগ্রহ প্রকাশ করুন (Interest)
            </button>
        @else
            <div class="space-y-3">
                <textarea
                    wire:model="note"
                    rows="3"
                    maxlength="500"
                    placeholder="ঐচ্ছিক নোট (যেমন: কবে থেকে শুরু করতে পারবেন)"
                    class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                ></textarea>
                @error('note')
                    <span class="text-red-600 text-xs">{{ $message }}</span>
                @enderror

                <div class="flex gap-2">
                    <button
                        wire:click="submit"
                        wire:loading.attr="disabled"
                        class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition disabled:opacity-50"
                    >
                        <span wire:loading.remove>জমা দিন</span>
                        <span wire:loading>পাঠানো হচ্ছে...</span>
                    </button>
                    <button
                        wire:click="$set('showNoteForm', false)"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50"
                    >
                        বাতিল
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>