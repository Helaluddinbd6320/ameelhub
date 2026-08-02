<x-filament-panels::page>
    @if ($this->record->is_verified)
        <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-emerald-800">
            {{ __('messages.agent_profile_edit.verified') }}
        </div>
    @elseif ($this->record->passport_copy && $this->record->nid_copy)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-800">
            {{ __('messages.agent_profile_edit.pending_review') }}
        </div>
    @else
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
            {{ __('messages.agent_profile_edit.not_submitted') }}
        </div>
    @endif

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button
                type="submit"
                :disabled="$this->record->is_verified"
            >
                {{ __('messages.agent_profile_edit.save_profile') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>