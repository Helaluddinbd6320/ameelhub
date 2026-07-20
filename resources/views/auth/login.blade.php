<x-guest-layout>
    {{-- lock icon --}}
    <div class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full"
         style="background-color:rgba(201,151,76,0.18); border:1px solid rgba(201,151,76,0.4);">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color:#C9974C;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="4" y="10" width="16" height="10" rx="2"/>
            <path d="M8 10V7a4 4 0 018 0v3" stroke-linecap="round"/>
        </svg>
    </div>

    <h1 class="text-center text-lg font-bold text-white mb-6" style="font-family: 'Noto Serif Bengali', serif;">
        আপনার একাউন্টে লগইন করুন
    </h1>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="mb-4 rounded-xl px-4 py-2.5 text-sm text-center" style="background-color:rgba(201,151,76,0.15); color:#e8c98a; border:1px solid rgba(201,151,76,0.3);">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-xs font-medium text-white/70 mb-1.5">ইমেইল</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="w-full rounded-2xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/35 outline-none transition-colors"
                       style="background-color:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.25);"
                       placeholder="you@example.com"
                       onfocus="this.style.borderColor='#C9974C'; this.style.backgroundColor='rgba(255,255,255,0.1)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.backgroundColor='rgba(255,255,255,0.06)'">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs" style="color:#f3a4a4;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-xs font-medium text-white/70 mb-1.5">পাসওয়ার্ড</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="w-full rounded-2xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/35 outline-none transition-colors"
                       style="background-color:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.25);"
                       placeholder="••••••••"
                       onfocus="this.style.borderColor='#C9974C'; this.style.backgroundColor='rgba(255,255,255,0.1)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.backgroundColor='rgba(255,255,255,0.06)'">
            </div>
            @error('password')
                <p class="mt-1.5 text-xs" style="color:#f3a4a4;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" style="accent-color:#C9974C;">
                <span class="text-sm text-white/70">মনে রাখুন</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-white/60 hover:text-white underline transition-colors" href="{{ route('password.request') }}">
                    পাসওয়ার্ড ভুলে গেছেন?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-2xl py-3 text-sm font-semibold transition-colors"
                style="background-color:#C9974C; color:#0B4F3F;"
                onmouseover="this.style.backgroundColor='#dbab5e'"
                onmouseout="this.style.backgroundColor='#C9974C'">
            লগইন করুন
        </button>

        {{-- Social Login --}}
        <div class="pt-2">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t" style="border-color:rgba(255,255,255,0.15);"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-3 text-white/40" style="background-color:transparent;">অথবা</span>
                </div>
            </div>
            <div class="mt-4 flex flex-col gap-2.5">
                <a href="{{ route('social.redirect', 'google') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-2xl text-sm font-medium transition-colors"
                    style="background-color:rgba(255,255,255,0.92); color:#374151;">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google দিয়ে লগইন
                </a>
                <a href="{{ route('social.redirect', 'facebook') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-2xl text-sm font-medium transition-colors"
                    style="background-color:rgba(255,255,255,0.92); color:#374151;">
                    <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook দিয়ে লগইন
                </a>
            </div>
        </div>

        <p class="text-center text-sm text-white/50 pt-2">
            একাউন্ট নেই? <a href="{{ route('register') }}" class="font-semibold hover:underline" style="color:#C9974C;">রেজিস্ট্রেশন করুন</a>
        </p>
    </form>
</x-guest-layout>