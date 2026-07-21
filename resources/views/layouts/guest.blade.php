<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AmeelHub') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@500;700&family=Hind+Siliguri:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased" style="font-family: 'Hind Siliguri', sans-serif;">

    {{-- ============ NAV BAR ============ --}}
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-100 relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center gap-2">
                        <a href="{{ url('/') }}" class="flex items-center gap-2">
                            <img src="{{ asset('images/logo.png') }}" alt="AmeelHub" class="h-10 w-auto">
                        </a>
                    </div>

                    <div class="hidden space-x-6 rtl:space-x-reverse sm:-my-px sm:ms-10 sm:flex">
                        <a href="{{ route('workers.index') }}"
                            class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-colors"
                            style="color:#4b5563;" onmouseover="this.style.color='#0B4F3F'"
                            onmouseout="this.style.color='#4b5563'">
                            কর্মীদের তালিকা
                        </a>
                        <a href="{{ route('jobs.index') }}"
                            class="inline-flex items-center px-1 pt-1 text-sm font-medium transition-colors"
                            style="color:#4b5563;" onmouseover="this.style.color='#0B4F3F'"
                            onmouseout="this.style.color='#4b5563'">
                            জব দেখুন
                        </a>
                    </div>
                </div>

                <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                    <a href="{{ route('login') }}" class="text-sm font-medium px-3 py-2 transition-colors"
                        style="color:{{ request()->routeIs('login') ? '#0B4F3F' : '#4b5563' }};"
                        onmouseover="this.style.color='#0B4F3F'"
                        onmouseout="this.style.color='{{ request()->routeIs('login') ? '#0B4F3F' : '#4b5563' }}'">
                        লগইন
                    </a>
                    <a href="{{ route('register') }}"
                        class="text-sm font-semibold rounded-lg px-4 py-2 transition-colors"
                        style="background-color:{{ request()->routeIs('register') ? '#0B4F3F' : '#C9974C' }}; color:{{ request()->routeIs('register') ? '#ffffff' : '#0B4F3F' }};">
                        রেজিস্ট্রেশন
                    </a>
                </div>

                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden border-t border-gray-100">
            <div class="pt-2 pb-3 space-y-1 px-4">
                <a href="{{ route('workers.index') }}" class="block py-2 text-sm font-medium text-gray-600">কর্মীদের
                    তালিকা</a>
                <a href="{{ route('jobs.index') }}" class="block py-2 text-sm font-medium text-gray-600">জব দেখুন</a>
            </div>
            <div class="pt-3 pb-4 border-t border-gray-100 px-4 space-y-2">
                <a href="{{ route('login') }}" class="block py-2 text-sm font-medium text-gray-600">লগইন</a>
                <a href="{{ route('register') }}"
                    class="block text-center text-sm font-semibold rounded-lg px-4 py-2.5"
                    style="background-color:#C9974C; color:#0B4F3F;">
                    রেজিস্ট্রেশন
                </a>
            </div>
        </div>
    </nav>

    {{-- ============ HERO + GLASS CARD ============ --}}
    <div class="min-h-[calc(100vh-4rem)] relative flex flex-col items-center justify-center px-4 py-10 overflow-hidden"
        style="background: linear-gradient(160deg, #0B4F3F 0%, #123f33 60%, #0a3529 100%);">

        <div class="pointer-events-none absolute inset-0"
            style="background: radial-gradient(circle at 12% 15%, rgba(201,151,76,0.30), transparent 42%), radial-gradient(circle at 88% 85%, rgba(255,255,255,0.08), transparent 45%);">
        </div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]"
            style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 26px 26px;">
        </div>

        {{-- fully transparent glass card --}}
        <div class="relative z-10 w-full sm:max-w-md rounded-[28px] px-6 py-9 sm:px-9"
            style="background-color:rgba(255,255,255,0.08); backdrop-filter: blur(22px) saturate(150%); -webkit-backdrop-filter: blur(22px) saturate(150%); box-shadow: 0 25px 70px rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.22);">
            {{ $slot }}
        </div>

        <p class="relative z-10 mt-6 text-xs text-white/45 text-center">
            লাইসেন্স নং ০০১৬২০৫ · সরকার অনুমোদিত রিক্রুটিং এজেন্সি
        </p>
    </div>

</body>

</html>
