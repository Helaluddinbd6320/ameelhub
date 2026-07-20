<x-app-layout>
    {{-- Main Wrapper with light slate background --}}
    <div class="min-h-screen pb-12 bg-slate-50 relative overflow-hidden">
        
        {{-- Premium Header Banner matching Dashboard --}}
        <div class="relative px-4 pt-10 pb-24 sm:px-6 lg:px-8 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-6 max-w-7xl mx-auto rounded-b-[40px] shadow-sm mb-[-4rem]" 
             style="background: linear-gradient(135deg, #0B4F3F 0%, #123f33 100%);">
            
            {{-- Decorative pattern overlay --}}
            <div class="pointer-events-none absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;"></div>
            
            <div class="relative z-10">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2" style="font-family: 'Noto Serif Bengali', serif;">
                    প্রোফাইল সেটিংস
                </h2>
                <p class="text-white/70 text-sm">
                    আপনার অ্যাকাউন্টের ব্যক্তিগত তথ্য, পাসওয়ার্ড এবং নিরাপত্তা সেটিংস এখান থেকে পরিবর্তন করুন।
                </p>
            </div>

            {{-- Profile Icon Pill --}}
            <div class="relative z-10 flex items-center gap-2 px-4 py-2 rounded-full border text-xs font-semibold"
                 style="background-color:rgba(201,151,76,0.15); color:#e8c98a; border-color:rgba(201,151,76,0.3);">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#C9974C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                অ্যাকাউন্ট ম্যানেজমেন্ট
            </div>
        </div>

        {{-- Profile Forms Container --}}
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 space-y-6">
            
            {{-- 1. Update Profile Information Section --}}
            <div class="bg-white rounded-[32px] shadow-xl shadow-gray-200/50 border border-gray-100/80 p-6 sm:p-10 backdrop-blur-sm transition-all duration-300 hover:shadow-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- 2. Update Password Section --}}
            <div class="bg-white rounded-[32px] shadow-xl shadow-gray-200/50 border border-gray-100/80 p-6 sm:p-10 backdrop-blur-sm transition-all duration-300 hover:shadow-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- 3. Delete Account Section (Danger Zone) --}}
            <div class="bg-white rounded-[32px] shadow-xl shadow-gray-200/50 border border-red-100/60 p-6 sm:p-10 backdrop-blur-sm transition-all duration-300 hover:shadow-xl" 
                 style="background-color: rgba(239, 68, 68, 0.01);">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

            {{-- Footer Info --}}
            <p class="text-center pt-4 text-xs text-gray-400">
                লাইসেন্স নং ০০১৬২০৫ · সরকার অনুমোদিত রিক্রুটিং एजेंसी
            </p>
        </div>
    </div>
</x-app-layout>