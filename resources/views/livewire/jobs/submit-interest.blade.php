<div>
    @if ($alreadyApplied)
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
            ✅ {{ __('messages.interest_button.already_applied') }}
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
                {{ __('messages.interest_button.express_interest') }}
            </button>
        @else
            <div class="space-y-3">
                <textarea
                    wire:model="note"
                    rows="3"
                    maxlength="500"
                    placeholder="{{ __('messages.interest_button.note_placeholder') }}"
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
                        <span wire:loading.remove>{{ __('messages.interest_button.submit') }}</span>
                        <span wire:loading>{{ __('messages.interest_button.sending') }}</span>
                    </button>
                    <button
                        wire:click="$set('showNoteForm', false)"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50"
                    >
                        {{ __('messages.interest_button.cancel') }}
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>