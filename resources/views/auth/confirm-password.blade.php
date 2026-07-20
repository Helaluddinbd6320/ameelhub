<x-guest-layout>
    {{-- Header / Icon --}}
    <div class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full"
         style="background-color:rgba(201,151,76,0.18); border:1px solid rgba(201,151,76,0.4);">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color:#C9974C;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3".5 y="11" width="17" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>

    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-white mb-2" style="font-family: 'Noto Serif Bengali', serif;">
            পাসওয়ার্ড নিশ্চিত করুন
        </h1>
        <p class="text-xs text-white/60 leading-relaxed max-w-sm mx-auto">
            এটি অ্যাপ্লিকেশনের একটি সুরক্ষিত এলাকা। সামনে এগিয়ে যাওয়ার আগে অনুগ্রহ করে আপনার পাসওয়ার্ডটি নিশ্চিত করুন।
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        {{-- Password --}}
        <div>
            <label for="password" class="block text-xs font-medium text-white/70 mb-1.5">পাসওয়ার্ড</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password" autofocus
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

        {{-- Submit Button --}}
        <button type="submit"
                class="w-full rounded-2xl py-3 text-sm font-semibold transition-colors pt-2"
                style="background-color:#C9974C; color:#0B4F3F;"
                onmouseover="this.style.backgroundColor='#dbab5e'"
                onmouseout="this.style.backgroundColor='#C9974C'">
            নিশ্চিত করুন
        </button>
    </form>
</x-guest-layout>