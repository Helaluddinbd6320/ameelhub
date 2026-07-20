<section>
    <header class="border-b border-gray-100 pb-4 mb-6">
        <h2 class="text-lg font-bold text-gray-900" style="font-family: 'Noto Serif Bengali', serif;">
            প্রোফাইল তথ্য (Profile Information)
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            আপনার অ্যাকাউন্টের নাম আপডেট করুন। নিরাপত্তার স্বার্থে ইমেইল ঠিকানা পরিবর্তন করা যাবে না।
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <!-- Name Input -->
        <div>
            <label for="name" class="block text-xs font-semibold text-gray-600 mb-1.5">নাম (Name)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                </span>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                       class="w-full rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 outline-none transition-all border border-gray-200 focus:border-[#0B4F3F] focus:ring-1 focus:ring-[#0B4F3F]"
                       placeholder="আপনার নাম লিখুন">
            </div>
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <!-- Email Input (Readonly - Cannot be edited) -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="email" class="block text-xs font-semibold text-gray-600">ইমেইল ঠিকানা (Email)</label>
                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60 flex items-center gap-1">
                    🔒 পরিবর্তনযোগ্য নয়
                </span>
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </span>
                {{-- readonly যুক্ত করায় এটি আর কোনো ইউজার এডিট করতে পারবে না --}}
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required readonly autocomplete="username"
                       class="w-full rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-500 border border-gray-200 bg-gray-50/80 cursor-not-allowed select-none outline-none">
            </div>
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            {{-- Unverified Email Alert (If applicable) --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 rounded-xl border border-amber-100 bg-amber-50/30">
                    <p class="text-xs text-amber-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span>⚠️ আপনার ইমেইল ঠিকানাটি এখনও ভেরিফাই করা হয়নি।</span>
                        <button form="send-verification" class="underline font-bold text-[#0B4F3F] hover:text-[#0e6350] transition-colors focus:outline-none">
                            ভেরিফিকেশন লিঙ্ক আবার পাঠান
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-semibold text-xs text-green-600">
                            ✓ আপনার ইমেইল ঠিকানায় একটি নতুন ভেরিফিকেশন লিঙ্ক পাঠানো হয়েছে।
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Submit Button & Status Status --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md shadow-emerald-800/10"
                    style="background-color:#0B4F3F;"
                    onmouseover="this.style.backgroundColor='#0e6350'; this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.backgroundColor='#0B4F3F'; this.style.transform='translateY(0)'">
                তথ্য সংরক্ষণ করুন
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                    ✓ সফলভাবে সংরক্ষিত হয়েছে
                </p>
            @endif
        </div>
    </form>
</section>