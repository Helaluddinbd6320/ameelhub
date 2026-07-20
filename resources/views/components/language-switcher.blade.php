@props(['mobile' => false])

@php
    $locales = config('app.available_locales', []);
    $current = app()->getLocale();
@endphp

@if($mobile)
    {{-- Compact mobile version: cycles through a simple button row --}}
    <div class="flex items-center gap-1">
        @foreach($locales as $code => $meta)
            <a href="{{ route('lang.switch', $code) }}"
                class="px-2 py-1 text-xs font-medium rounded-md border
                    {{ $current === $code
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'bg-white text-gray-500 border-gray-200 hover:text-gray-800' }}">
                {{ strtoupper($code) }}
            </a>
        @endforeach
    </div>
@else
    <x-dropdown align="right" width="40">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1 px-3 py-2 border border-gray-200 text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-800 hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.5 7a10.05 10.05 0 01-4.412 6.5" />
                </svg>
                <span>{{ $locales[$current]['native'] ?? strtoupper($current) }}</span>
                <svg class="fill-current h-3 w-3 rtl:scale-x-[-1]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            @foreach($locales as $code => $meta)
                <a href="{{ route('lang.switch', $code) }}"
                    class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out
                        {{ $current === $code ? 'font-semibold text-blue-600' : '' }}">
                    {{ $meta['native'] ?? strtoupper($code) }}
                </a>
            @endforeach
        </x-slot>
    </x-dropdown>
@endif