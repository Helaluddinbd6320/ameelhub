<x-guest-layout>
    {{-- Header / Description --}}
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-white mb-2" style="font-family: 'Noto Serif Bengali', serif;">
            পাসওয়ার্ড রিসেট করুন
        </h1>
        <p class="text-xs text-white/60 leading-relaxed max-w-sm mx-auto">
            আপনার অ্যাকাউন্টের ইমেইল ঠিকানাটি লিখুন। আমরা আপনাকে একটি পাসওয়ার্ড রিসেট লিঙ্ক পাঠাবো, যার মাধ্যমে নতুন পাসওয়ার্ড সেট করতে পারবেন।
        </p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="mb-4 rounded-xl px-4 py-2.5 text-xs text-center border" 
             style="background-color:rgba(16,185,129,0.12); color:#a7f3d0; border-color:rgba(16,185,129,0.3);">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        {{-- Email Address --}}
        <div>
            <label for="email" class="block text-xs font-medium text-white/70 mb-1.5">ইমেইল ঠিকানা</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
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

        {{-- Submit Button --}}
        <button type="submit"
                class="w-full rounded-2xl py-3 text-sm font-semibold transition-colors mt-2"
                style="background-color:#C9974C; color:#0B4F3F;"
                onmouseover="this.style.backgroundColor='#dbab5e'"
                onmouseout="this.style.backgroundColor='#C9974C'">
            রিসেট লিঙ্ক পাঠান
        </button>

        {{-- Back to Login --}}
        <p class="text-center text-sm text-white/50 pt-2">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1 hover:underline" style="color:#C9974C;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                লগইন পেজে ফিরে যান
            </a>
        </p>
    </form>
</x-guest-layout>