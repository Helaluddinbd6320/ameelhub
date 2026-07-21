<x-guest-layout>
    {{-- Header --}}
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-white" style="font-family: 'Noto Serif Bengali', serif;">
            নতুন একাউন্ট তৈরি করুন
        </h1>
        <p class="text-xs text-white/50 mt-1">নিরাপদ, লাইসেন্সপ্রাপ্ত প্ল্যাটফর্মে যোগ দিন</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Referral Code (hidden) -->
        <input type="hidden" name="ref" value="{{ old('ref', $referralCode ?? '') }}">

        @if (old('ref', $referralCode ?? null))
            <div class="rounded-xl px-4 py-2.5 text-xs text-center border" 
                 style="background-color:rgba(16,185,129,0.12); color:#a7f3d0; border-color:rgba(16,185,129,0.3);">
                ✓ রেফারেল কোড প্রয়োগ হয়েছে — রেজিস্ট্রেশন সম্পন্ন হলে আপনার পরিচিতজন বোনাস পাবেন।
            </div>
        @endif

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-medium text-white/70 mb-1.5">নাম (Name)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="w-full rounded-2xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-white/35 outline-none transition-colors"
                       style="background-color:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.25);"
                       placeholder="আপনার পুরো নাম লিখুন"
                       onfocus="this.style.borderColor='#C9974C'; this.style.backgroundColor='rgba(255,255,255,0.1)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.25)'; this.style.backgroundColor='rgba(255,255,255,0.06)'">
            </div>
            @error('name')
                <p class="mt-1.5 text-xs" style="color:#f3a4a4;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-medium text-white/70 mb-1.5">ইমেইল (Email)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-white/40" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
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

        <!-- Role Selection -->
        <div>
            <label class="block text-xs font-medium text-white/70 mb-1.5">আমি কে? / I am a</label>
            <div class="flex gap-3">
                {{-- Worker Card --}}
                <label id="label-worker" class="flex-1 flex items-center gap-2 cursor-pointer rounded-2xl border px-3.5 py-3 transition-all"
                       style="{{ old('role', 'worker') === 'worker' ? 'border-color:#C9974C; background-color:rgba(201,151,76,0.12);' : 'border-color:rgba(255,255,255,0.2); background-color:rgba(255,255,255,0.04);' }}">
                    <input type="radio" name="role" value="worker" id="role-worker"
                        {{ old('role', 'worker') === 'worker' ? 'checked' : '' }}
                        style="accent-color:#C9974C;"
                        onchange="updateRoles('worker')"/>
                    <span class="text-xs font-medium text-white">🧑‍🔧 কর্মী (Worker)</span>
                </label>
                
                {{-- Agent Card --}}
                <label id="label-agent" class="flex-1 flex items-center gap-2 cursor-pointer rounded-2xl border px-3.5 py-3 transition-all"
                       style="{{ old('role') === 'agent' ? 'border-color:#C9974C; background-color:rgba(201,151,76,0.12);' : 'border-color:rgba(255,255,255,0.2); background-color:rgba(255,255,255,0.04);' }}">
                    <input type="radio" name="role" value="agent" id="role-agent"
                        {{ old('role') === 'agent' ? 'checked' : '' }}
                        style="accent-color:#C9974C;"
                        onchange="updateRoles('agent')"/>
                    <span class="text-xs font-medium text-white">🤝 এজেন্ট (Agent)</span>
                </label>
            </div>
            @error('role')
                <p class="mt-1.5 text-xs" style="color:#f3a4a4;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-medium text-white/70 mb-1.5">পাসওয়ার্ড (Password)</label>
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
            রেজিস্ট্রেশন করুন
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
                {{--
                    BUG FIX (Step 10.9 audit — Helal-reported): these two links
                    used to point straight at route('social.redirect', ...)
                    with no role info at all, so SocialAuthController always
                    hardcoded new social signups as 'worker' — the radio
                    selection above was silently ignored. We now keep the
                    base URL in a data attribute and append "?role=worker" or
                    "?role=agent" via JS, kept in sync with the radio buttons
                    (see updateRoles() below), so whichever card is selected
                    when the person clicks Google/Facebook is what actually
                    gets sent to the backend.
                --}}
                <a id="social-google" href="{{ route('social.redirect', 'google') }}"
                    data-base-url="{{ route('social.redirect', 'google') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-2xl text-sm font-medium transition-colors"
                    style="background-color:rgba(255,255,255,0.92); color:#374151;">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google দিয়ে রেজিস্ট্রেশন
                </a>
                <a id="social-facebook" href="{{ route('social.redirect', 'facebook') }}"
                    data-base-url="{{ route('social.redirect', 'facebook') }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-2xl text-sm font-medium transition-colors"
                    style="background-color:rgba(255,255,255,0.92); color:#374151;">
                    <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook দিয়ে রেজিস্ট্রেশন
                </a>
            </div>
        </div>

        {{-- Footer Link --}}
        <p class="text-center text-sm text-white/50 pt-2">
            ইতিমধ্যেই রেজিস্টার্ড? <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color:#C9974C;">লগইন করুন</a>
        </p>
    </form>

    {{-- Script for Radio Button Background Styling + Social Link role sync --}}
    <script>
        function updateSocialLinks(role) {
            ['social-google', 'social-facebook'].forEach(function (id) {
                const link = document.getElementById(id);
                if (!link) return;
                const base = link.getAttribute('data-base-url');
                link.setAttribute('href', base + '?role=' + encodeURIComponent(role));
            });
        }

        function updateRoles(selected) {
            const workerLabel = document.getElementById('label-worker');
            const agentLabel = document.getElementById('label-agent');
            
            if (selected === 'worker') {
                workerLabel.style.borderColor = '#C9974C';
                workerLabel.style.backgroundColor = 'rgba(201,151,76,0.12)';
                agentLabel.style.borderColor = 'rgba(255,255,255,0.2)';
                agentLabel.style.backgroundColor = 'rgba(255,255,255,0.04)';
            } else {
                agentLabel.style.borderColor = '#C9974C';
                agentLabel.style.backgroundColor = 'rgba(201,151,76,0.12)';
                workerLabel.style.borderColor = 'rgba(255,255,255,0.2)';
                workerLabel.style.backgroundColor = 'rgba(255,255,255,0.04)';
            }

            updateSocialLinks(selected);
        }

        // Set the initial href on page load to match whichever radio is
        // checked by default (respects old('role') on validation-error
        // redisplay too, since the radio 'checked' state above already
        // reflects old('role', 'worker')).
        document.addEventListener('DOMContentLoaded', function () {
            const checked = document.querySelector('input[name="role"]:checked');
            updateSocialLinks(checked ? checked.value : 'worker');
        });
    </script>
</x-guest-layout>