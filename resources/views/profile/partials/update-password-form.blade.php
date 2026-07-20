<section>
    <header class="border-b border-gray-100 pb-4 mb-6">
        <h2 class="text-lg font-bold text-gray-900" style="font-family: 'Noto Serif Bengali', serif;">
            পাসওয়ার্ড পরিবর্তন (Update Password)
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            আপনার অ্যাকাউন্টের নিরাপত্তা নিশ্চিত করতে একটি দীর্ঘ এবং এলোমেলো (Random) পাসওয়ার্ড ব্যবহার করুন।
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div>
            <label for="update_password_current_password" class="block text-xs font-semibold text-gray-600 mb-1.5">বর্তমান পাসওয়ার্ড (Current Password)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                       class="w-full rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 outline-none transition-all border border-gray-200 focus:border-[#0B4F3F] focus:ring-1 focus:ring-[#0B4F3F]"
                       placeholder="আপনার বর্তমান পাসওয়ার্ডটি দিন">
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <!-- New Password -->
        <div>
            <label for="update_password_password" class="block text-xs font-semibold text-gray-600 mb-1.5">নতুন পাসওয়ার্ড (New Password)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </span>
                <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                       class="w-full rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 outline-none transition-all border border-gray-200 focus:border-[#0B4F3F] focus:ring-1 focus:ring-[#0B4F3F]"
                       placeholder="কমপক্ষে ৮ অক্ষরের নতুন পাসওয়ার্ড দিন">
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-semibold text-gray-600 mb-1.5">নতুন পাসওয়ার্ড নিশ্চিত করুন (Confirm Password)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                       class="w-full rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 outline-none transition-all border border-gray-200 focus:border-[#0B4F3F] focus:ring-1 focus:ring-[#0B4F3F]"
                       placeholder="নতুন পাসওয়ার্ডটি আবার লিখুন">
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <!-- Submit Button & Success Message -->
        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all shadow-md shadow-emerald-800/10"
                    style="background-color:#0B4F3F;"
                    onmouseover="this.style.backgroundColor='#0e6350'; this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.backgroundColor='#0B4F3F'; this.style.transform='translateY(0)'">
                পাসওয়ার্ড আপডেট করুন
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                    ✓ পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে
                </p>
            @endif
        </div>
    </form>
</section>