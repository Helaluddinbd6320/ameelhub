<x-guest-layout>
    {{-- Header / Icon --}}
    <div class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full"
         style="background-color:rgba(201,151,76,0.18); border:1px solid rgba(201,151,76,0.4);">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" style="color:#C9974C;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
    </div>

    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-white mb-2" style="font-family: 'Noto Serif Bengali', serif;">
            ইমেইল ভেরিফিকেশন
        </h1>
        <p class="text-xs text-white/60 leading-relaxed max-w-sm mx-auto">
            নিবন্ধন করার জন্য ধন্যবাদ! শুরু করার আগে, আমরা আপনার ইমেইলে একটি ভেরিফিকেশন লিঙ্ক পাঠিয়েছি, সেটি ক্লিক করে অ্যাকাউন্টটি সচল করুন। যদি লিঙ্কটি না পেয়ে থাকেন, তবে নিচের বাটনে ক্লিক করে আবার পাঠাতে পারেন।
        </p>
    </div>

    {{-- Session Status --}}
    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 rounded-xl px-4 py-2.5 text-xs text-center border" 
             style="background-color:rgba(16,185,129,0.12); color:#a7f3d0; border-color:rgba(16,185,129,0.3);">
            ✓ রেজিস্ট্রেশনের সময় দেওয়া আপনার ইমেইল ঠিকানায় একটি নতুন ভেরিফিকেশন লিঙ্ক পাঠানো হয়েছে।
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4">
        {{-- Resend Button --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full rounded-2xl py-3 text-sm font-semibold transition-colors"
                    style="background-color:#C9974C; color:#0B4F3F;"
                    onmouseover="this.style.backgroundColor='#dbab5e'"
                    onmouseout="this.style.backgroundColor='#C9974C'">
                ভেরিফিকেশন ইমেইল আবার পাঠান
            </button>
        </form>

        {{-- Log Out Link --}}
        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="text-sm text-white/50 hover:text-white underline transition-colors focus:outline-none">
                লগ আউট করুন
            </button>
        </form>
    </div>
</x-guest-layout>