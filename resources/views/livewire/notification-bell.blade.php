<div
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative shrink-0"
    wire:poll.30s="refreshCount"
>
    <button
        type="button"
        @click="open = !open"
        aria-haspopup="true"
        :aria-expanded="open"
        class="relative flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-gray-100 transition-all duration-200 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-12' : ''">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>

        @if ($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative flex items-center justify-center rounded-full h-4 w-4 bg-red-500 text-white text-[9px] font-bold ring-2 ring-white dark:ring-gray-900">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        role="menu"
        class="absolute right-0 top-12 z-50 w-[22rem] max-w-[90vw] rounded-2xl bg-white dark:bg-gray-900 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] dark:shadow-[0_10px_40px_-10px_rgba(0,0,0,0.5)] border border-gray-100 dark:border-white/10 overflow-hidden"
    >
        {{-- Header: title pinned left, action pinned right --}}
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02]">
            <span class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                নোটিফিকেশন
                @if ($unreadCount > 0)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 text-xs font-bold tabular-nums">
                        {{ $unreadCount }}টি নতুন
                    </span>
                @endif
            </span>

            @if ($unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="shrink-0 whitespace-nowrap text-xs font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 hover:underline transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded"
                >
                    সব পড়া হয়েছে
                </button>
            @endif
        </div>

        <div class="divide-y divide-gray-100/80 dark:divide-white/5 max-h-[26rem] overflow-y-auto">
            @php
                $grouped = $notifications->groupBy(
                    fn ($n) => $n->created_at->isToday() ? 'আজ' : ($n->created_at->isYesterday() ? 'গতকাল' : 'আরও আগে')
                );
            @endphp

            @forelse ($grouped as $groupLabel => $groupNotifications)
                <div>
                    <p class="px-5 pt-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 bg-gray-50/30 dark:bg-white/[0.01]">
                        {{ $groupLabel }}
                    </p>

                    @foreach ($groupNotifications as $notification)
                        <button
                            type="button"
                            role="menuitem"
                            wire:click="markAsRead({{ $notification->id }})"
                            @click="open = false"
                            class="w-full text-left px-5 py-3.5 hover:bg-gray-50/80 dark:hover:bg-white/[0.03] transition-all duration-150 group focus:outline-none focus-visible:bg-gray-50 dark:focus-visible:bg-white/[0.03]
                                {{ $notification->isRead() ? '' : 'bg-primary-50/30 dark:bg-primary-500/[0.02]' }}"
                        >
                            <div class="flex items-start gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full shrink-0 mt-0.5
                                    {{ $notification->isRead() ? 'bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500' : 'bg-primary-100 text-primary-600 dark:bg-primary-500/15 dark:text-primary-400' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                    </svg>
                                </span>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-150 break-words whitespace-normal">
                                        {{ $notification->title }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 leading-relaxed break-words whitespace-normal">
                                        {{ $notification->body }}
                                    </p>
                                    <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mt-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                @unless ($notification->isRead())
                                    <span class="mt-2 w-2 h-2 rounded-full bg-primary-500 shrink-0 ring-4 ring-primary-500/20"></span>
                                @endunless
                            </div>
                        </button>
                    @endforeach
                </div>
            @empty
                <div class="flex flex-col items-center justify-center gap-3 px-6 py-12 text-center">
                    <div class="flex items-center justify-center w-14 h-14 rounded-full bg-gray-50 dark:bg-white/5 text-gray-400 dark:text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-200">কোনো নোটিফিকেশন নেই</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">নতুন কোনো নোটিফিকেশন আসলে তা এখানে জমা হবে।</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Optional footer — uncomment and point to your notifications index route if you have one --}}
        {{--
        <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5 text-center">
            <a href="#" class="text-xs font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 hover:underline">
                সব নোটিফিকেশন দেখুন
            </a>
        </div>
        --}}
    </div>
</div>