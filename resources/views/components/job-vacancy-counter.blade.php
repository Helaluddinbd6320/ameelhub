@php
    $barColor = [
        'full'    => 'bg-gray-400',
        'urgent'  => 'bg-red-500',
        'warning' => 'bg-amber-500',
        'safe'    => 'bg-emerald-500',
    ][$urgency];

    $textColor = [
        'full'    => 'text-gray-500',
        'urgent'  => 'text-red-700',
        'warning' => 'text-amber-700',
        'safe'    => 'text-emerald-700',
    ][$urgency];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="mb-1 flex items-center justify-between text-xs font-medium {{ $textColor }}">
        <span>{{ $label }}</span>
        <span>{{ $filledCount }}/{{ $vacancies }}</span>
    </div>
    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
        <div
            class="h-2 rounded-full {{ $barColor }} transition-all duration-300"
            style="width: {{ min(100, $percentFilled) }}%"
        ></div>
    </div>
</div>