<x-guest-layout>
    {{-- Header --}}
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-white" style="font-family: 'Noto Serif Bengali', serif;">
            নতুন পাসওয়ার্ড সেট করুন
        </h1>
        <p class="text-xs text-white/50 mt-1">আপনার নতুন নিরাপদ পাসওয়ার্ডটি এখানে লিখুন</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-medium text-white/70 mb-1.5">ইমেইল ঠিকানা</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       class="w-full rounded-2xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-white/35 outline-none transition-colors"
                       style="background-color:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.25);"
                       placeholder="you@example.com"
                       onfocus="this.style.borderColor='#C9974C'; this.style.backgroundColor='rgba(255,255,255,0.1)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.backgroundColor='rgba(255,255,255,0.06)'">
            </div>
            @error('email')
                <p class="mt-1.5 text-xs" style="color:#f3a4a4;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-medium text-white/70 mb-1.5">নতুন পাসওয়ার্ড</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="w-full rounded-2xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-white/35 outline-none transition-colors"
                       style="background-color:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.25);"
                       placeholder="••••••••"
                       onfocus="this.style.borderColor='#C9974C'; this.style.backgroundColor='rgba(255,255,255,0.1)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.backgroundColor='rgba(255,255,255,0.06)'">
            </div>
            @error('password')
                <p class="mt-1.5 text-xs" style="color:#f3a4a4;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-white/70 mb-1.5">পাসওয়ার্ড নিশ্চিত করুন</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-2xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-white/35 outline-none transition-colors"
                       style="background-color:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.25);"
                       placeholder="••••••••"
                       onfocus="this.style.borderColor='#C9974C'; this.style.backgroundColor='rgba(255,255,255,0.1)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.backgroundColor='rgba(255,255,255,0.06)'">
            </div>
            @error('password_confirmation')
                <p class="mt-1.5 text-xs" style="color:#f3a4a4;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit Button --}}
        <button type="submit"
                class="w-full rounded-2xl py-3 text-sm font-semibold transition-colors mt-2"
                style="background-color:#C9974C; color:#0B4F3F;"
                onmouseover="this.style.backgroundColor='#dbab5e'"
                onmouseout="this.style.backgroundColor='#C9974C'">
            পাসওয়ার্ড রিসেট করুন
        </button>
    </form>
</x-guest-layout>