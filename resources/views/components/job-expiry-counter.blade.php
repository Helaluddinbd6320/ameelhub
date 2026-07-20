@php
    $styles = [
        'none'    => 'bg-gray-100 text-gray-500',
        'expired' => 'bg-gray-200 text-gray-600',
        'urgent'  => 'bg-red-50 text-red-700 ring-1 ring-red-200',
        'warning' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        'safe'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    ];

    $dot = [
        'none'    => 'bg-gray-300',
        'expired' => 'bg-gray-400',
        'urgent'  => 'bg-red-500 animate-pulse',
        'warning' => 'bg-amber-500',
        'safe'    => 'bg-emerald-500',
    ];
@endphp

<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium {$styles[$urgency]}"]) }}
>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot[$urgency] }}"></span>
    {{ $label }}
</span>