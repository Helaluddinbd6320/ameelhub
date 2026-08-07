@php
    $tickerEnabled = \App\Models\Setting::get('alert_ticker_enabled', '0');
    $tickerMessage = \App\Models\Setting::get('alert_ticker_message', '');
    $tickerWhatsapp = \App\Models\Setting::get('alert_ticker_whatsapp', '');
@endphp

@if($tickerEnabled === '1' && trim($tickerMessage) !== '')
    <div class="ameelhub-ticker">
        <div class="ameelhub-ticker__track">
            <span class="ameelhub-ticker__item">
                {{ $tickerMessage }}
                @if($tickerWhatsapp)
                    
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $tickerWhatsapp) }}"
                        target="_blank"
                        rel="noopener"
                        class="ameelhub-ticker__link"
                    >
                        {{ $tickerWhatsapp }}
                    </a>
                @endif
            </span>
            {{-- সিমলেস লুপের জন্য একই কন্টেন্ট দ্বিতীয়বার (aria-hidden, screen reader দুইবার পড়বে না) --}}
            <span class="ameelhub-ticker__item" aria-hidden="true">
                {{ $tickerMessage }}
                @if($tickerWhatsapp)
                    
                       <a href="https://wa.me/{{ preg_replace('/\D/', '', $tickerWhatsapp) }}"
                        target="_blank"
                        rel="noopener"
                        class="ameelhub-ticker__link"
                        tabindex="-1"
                    >
                        {{ $tickerWhatsapp }}
                    </a>
                @endif
            </span>
        </div>
    </div>

    <style>
        .ameelhub-ticker {
            overflow: hidden;
            white-space: nowrap;
            background: #111827;
            color: #f9fafb;
            padding: 0.5rem 0;
            width: 100%;
        }
        .dark .ameelhub-ticker {
            background: #0b1220;
        }
        .ameelhub-ticker__track {
            display: inline-flex;
            width: max-content;
            animation: ameelhub-ticker-scroll 22s linear infinite;
        }
        .ameelhub-ticker__item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding-inline-end: 4rem;
            font-size: 0.8125rem;
            font-weight: 500;
        }
        .ameelhub-ticker__link {
            color: #34d399;
            text-decoration: underline;
            font-weight: 700;
        }
        @keyframes ameelhub-ticker-scroll {
            from { transform: translateX(0%); }
            to   { transform: translateX(-50%); }
        }
        /* motion sensitivity — accessibility */
        @media (prefers-reduced-motion: reduce) {
            .ameelhub-ticker__track { animation: none; }
        }
    </style>
@endif