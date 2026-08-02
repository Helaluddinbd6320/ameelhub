<div class="max-w-5xl mx-auto px-4 py-10">

    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('messages.agent_leaderboard.title') }}</h1>
        <p class="text-gray-500 mt-2">{{ __('messages.agent_leaderboard.subtitle') }}</p>
    </div>

    @if($agents->isEmpty())
        <div class="text-center py-16 text-gray-400">
            {{ __('messages.agent_leaderboard.no_agents') }}
        </div>
    @else

        <div class="space-y-3">
            @foreach($agents as $index => $agent)
                @php
                    $rank = ($agents->currentPage() - 1) * $agents->perPage() + $index + 1;
                    $medal = match($rank) {
                        1 => ['bg' => 'bg-yellow-50 border-yellow-300', 'badge' => 'bg-yellow-400 text-white', 'label' => '🥇'],
                        2 => ['bg' => 'bg-gray-50 border-gray-300', 'badge' => 'bg-gray-400 text-white', 'label' => '🥈'],
                        3 => ['bg' => 'bg-orange-50 border-orange-300', 'badge' => 'bg-orange-400 text-white', 'label' => '🥉'],
                        default => ['bg' => 'bg-white border-gray-200', 'badge' => 'bg-emerald-600 text-white', 'label' => null],
                    };
                @endphp

                <a href="{{ route('agents.show', $agent->uuid) }}"
                   class="flex items-center gap-4 p-4 rounded-xl border {{ $medal['bg'] }} hover:shadow-md transition">

                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full font-bold {{ $medal['badge'] }}">
                        {{ $medal['label'] ?? $rank }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800 truncate">
                                {{ $agent->agent_name_bn ?? $agent->agent_name_en ?? __('messages.agent_leaderboard.no_name') }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                                ✓ {{ __('messages.agent_leaderboard.verified') }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 truncate">
                            {{ $agent->company_name ?? '—' }}
                            @if($agent->city)
                                · {{ $agent->city }}
                            @endif
                        </div>
                    </div>

                    <div class="flex-shrink-0 flex items-center gap-6 text-center">
                        <div>
                            <div class="text-lg font-bold text-gray-800">{{ $agent->successful_deals_count }}</div>
                            <div class="text-xs text-gray-400">{{ __('messages.agent_leaderboard.successful_deals') }}</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-800">{{ $agent->workers_placed_count }}</div>
                            <div class="text-xs text-gray-400">{{ __('messages.agent_leaderboard.workers_placed') }}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $agents->links() }}
        </div>

    @endif
</div>