<x-filament-panels::page>
    @if ($this->record->is_verified)
        <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-emerald-800">
            ✅ আপনার প্রোফাইল ভেরিফাইড হয়েছে। এখন আপনি জব পোস্ট করতে পারবেন।
        </div>
    @elseif ($this->record->passport_copy && $this->record->nid_copy)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-800">
            ⏳ আপনার নথি জমা হয়েছে, অ্যাডমিন যাচাই করছে। যাচাই সম্পন্ন হলে জানানো হবে।
        </div>
    @else
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
            ⚠️ ভেরিফিকেশনের জন্য পাসপোর্ট ও NID কপি আপলোড করে জমা দিন। ভেরিফাইড না হলে জব পোস্ট করা যাবে না।
        </div>
    @endif

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button
                type="submit"
                :disabled="$this->record->is_verified"
            >
                প্রোফাইল সংরক্ষণ করুন
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
