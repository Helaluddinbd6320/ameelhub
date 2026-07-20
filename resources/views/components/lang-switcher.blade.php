<div class="relative inline-block text-left" x-data="{ open: false }">
    <button
        @click="open = !open"
        type="button"
        class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
    >
        {{ config('app.available_locales')[app()->getLocale()]['label'] }}
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="absolute right-0 z-10 mt-2 w-32 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5"
        style="display: none;"
    >
        <div class="py-1">
            @foreach (config('app.available_locales') as $code => $meta)
                
                    href="{{ route('lang.switch', $code) }}"
                    class="block px-4 py-2 text-sm {{ app()->getLocale() === $code ? 'bg-gray-100 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}"
                >
                    {{ $meta['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>