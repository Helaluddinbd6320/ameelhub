<x-app-layout>
    {{-- Main Wrapper with deep gradient matching AmeelHub Theme --}}
    <div class="min-h-screen pb-12 bg-slate-50 relative overflow-hidden">

        {{-- Premium Hero / Welcome Section --}}
        <div class="relative px-4 pt-10 pb-24 sm:px-6 lg:px-8 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-6 max-w-7xl mx-auto rounded-b-[40px] shadow-sm mb-[-4rem]"
            style="background: linear-gradient(135deg, #0B4F3F 0%, #123f33 100%);">

            {{-- Decorative pattern overlay --}}
            <div class="pointer-events-none absolute inset-0 opacity-[0.04]"
                style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;">
            </div>

            <div class="relative z-10">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-2"
                    style="font-family: 'Noto Serif Bengali', serif;">
                    {{ __('messages.dashboard.title') }}
                </h2>
                <p class="text-white/70 text-sm">
                    {{ __('messages.dashboard.welcome') }}, <span
                        class="text-white font-bold underline decoration-[#C9974C] decoration-2">{{ Auth::user()->name }}</span>!
                    {{ __('messages.dashboard.welcome_message') }}
                </p>
            </div>

            {{-- Verified Status Pill --}}
            <div class="relative z-10 flex items-center gap-2 px-4 py-2 rounded-full border text-xs font-semibold"
                style="background-color:rgba(201,151,76,0.15); color:#e8c98a; border-color:rgba(201,151,76,0.3);">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#C9974C]" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M6.267 3.455a.75.75 0 00-.708.523.75.75 0 00.366.886l3.5 2a.75.75 0 00.742 0l3.5-2a.75.75 0 00.366-.886.75.75 0 00-.708-.523H6.267zM3.25 9.25a.75.75 0 011 0L10 14.25l5.75-5a.75.75 0 111 1.1l-6.25 5.43a.75.75 0 01-.96 0L3.25 10.35a.75.75 0 010-1.1z"
                        clip-rule="evenodd" />
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                        clip-rule="evenodd" />
                </svg>
                {{ __('messages.dashboard.account_verified') }}
            </div>
        </div>

        {{-- Main Container (Elevated Grid/Card area) --}}
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
            <div
                class="bg-white rounded-[32px] shadow-xl shadow-gray-200/50 border border-gray-100/80 p-6 sm:p-10 backdrop-blur-sm">

                <div class="text-center sm:text-left border-b border-gray-100 pb-5 mb-6">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                        {{ __('messages.dashboard.access_control_panel') }}
                    </h4>
                    <p class="text-sm text-gray-500 mt-1">{{ __('messages.dashboard.access_hint') }}
                    </p>
                </div>

                {{-- ১. অ্যাডমিন প্যানেল (super_admin অথবা admin যেকোনো একটি হলেই শো করবে) --}}
                @if (strtolower(Auth::user()->role) === 'super_admin' || strtolower(Auth::user()->role) === 'admin')
                    <div
                        class="group flex flex-col sm:flex-row items-center justify-between p-5 rounded-2xl border border-red-100 bg-red-50/20 hover:bg-red-50/40 transition-all duration-300 gap-4">
                        <div class="flex items-center gap-4 text-center sm:text-left flex-col sm:flex-row">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 text-base">{{ __('messages.dashboard.admin_central') }}</h5>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.dashboard.admin_desc') }}</p>
                            </div>
                        </div>
                        <a href="{{ url('/admin') }}"
                            class="w-full sm:w-auto text-center px-6 py-3 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 shadow-md shadow-red-600/10 transition-all whitespace-nowrap">
                            {{ __('messages.dashboard.go_to_admin') }}
                        </a>
                    </div>
                @endif

                {{-- ২. এজেন্ট প্যানেল --}}
                @if (strtolower(Auth::user()->role) === 'agent')
                    <div
                        class="group flex flex-col sm:flex-row items-center justify-between p-6 rounded-2xl border border-amber-100 bg-amber-50/10 hover:bg-amber-50/30 transition-all duration-300 gap-4">
                        <div class="flex items-center gap-4 text-center sm:text-left flex-col sm:flex-row">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-[#C9974C]"
                                style="background-color: rgba(201,151,76,0.12);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 text-base">{{ __('messages.dashboard.agent_workspace') }}</h5>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.dashboard.agent_desc') }}</p>
                            </div>
                        </div>
                        <a href="{{ url('/agent') }}"
                            class="w-full sm:w-auto text-center px-6 py-3 rounded-xl text-sm font-semibold transition-all shadow-md shadow-amber-600/5 whitespace-nowrap"
                            style="background-color:#C9974C; color:#0B4F3F;"
                            onmouseover="this.style.backgroundColor='#dbab5e'; this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.backgroundColor='#C9974C'; this.style.transform='translateY(0)'">
                            {{ __('messages.dashboard.go_to_agent') }}
                        </a>
                    </div>
                @endif

                {{-- ৩. কর্মী (Worker) প্যানেল --}}
                @if (strtolower(Auth::user()->role) === 'worker')
                    <div
                        class="group flex flex-col sm:flex-row items-center justify-between p-6 rounded-2xl border border-emerald-100 bg-emerald-50/10 hover:bg-emerald-50/30 transition-all duration-300 gap-4">
                        <div class="flex items-center gap-4 text-center sm:text-left flex-col sm:flex-row">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-[#0B4F3F]"
                                style="background-color: rgba(11,79,63,0.1);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 text-base">{{ __('messages.dashboard.worker_profile_portal') }}</h5>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.dashboard.worker_desc') }}</p>
                            </div>
                        </div>
                        <a href="{{ url('/worker') }}"
                            class="w-full sm:w-auto text-center px-6 py-3 rounded-xl text-sm font-semibold text-white transition-all shadow-md shadow-emerald-800/10 whitespace-nowrap"
                            style="background-color:#0B4F3F;"
                            onmouseover="this.style.backgroundColor='#0e6350'; this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.backgroundColor='#0B4F3F'; this.style.transform='translateY(0)'">
                            {{ __('messages.dashboard.go_to_worker') }}
                        </a>
                    </div>
                @endif

            </div>

            {{-- Small footer info inside container --}}
            <p class="text-center mt-6 text-xs text-gray-400">
                {{ __('messages.dashboard.footer_license') }}
            </p>
        </div>
    </div>
</x-app-layout>