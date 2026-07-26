@php
    $isAgent = ($panel ?? 'worker') === 'agent';
    $scope = '/' . ($isAgent ? 'agent' : 'worker') . '/';
    $accent = $isAgent ? '#00845A' : '#F54A00';
    $icon192 = asset('icons/icon-' . ($isAgent ? 'agent' : 'worker') . '-192.png');
    $storageKey = 'ameelhub_pwa_dismissed_' . ($isAgent ? 'agent' : 'worker');
@endphp

<div
    id="ameelhub-pwa-install-banner"
    class="hidden"
    style="position:fixed; bottom:16px; left:16px; right:16px; z-index:9999; max-width:360px; margin-left:auto; border-radius:14px; box-shadow:0 8px 24px rgba(0,0,0,0.15); border:1px solid #e5e7eb; background:#ffffff; padding:14px;"
>
    <div style="display:flex; align-items:flex-start; gap:10px;">
        <img src="{{ $icon192 }}" alt="" width="40" height="40" style="border-radius:10px; flex-shrink:0;">
        <div style="flex:1; min-width:0;">
            <p style="font-size:13px; font-weight:600; color:#111827; margin:0;">AmeelHub অ্যাপ ইনস্টল করুন</p>
            <p id="ameelhub-pwa-banner-text" style="font-size:12px; color:#6b7280; margin:4px 0 0;">
                হোম স্ক্রিন থেকে সরাসরি খুলুন, দ্রুত অ্যাক্সেসের জন্য।
            </p>
            <div style="margin-top:8px; display:flex; gap:8px;">
                <button type="button" id="ameelhub-pwa-install-btn"
                    style="font-size:12px; font-weight:600; padding:6px 12px; border-radius:8px; border:none; color:#fff; background:{{ $accent }};">
                    ইনস্টল করুন
                </button>
                <button type="button" id="ameelhub-pwa-dismiss-btn"
                    style="font-size:12px; font-weight:600; padding:6px 12px; border-radius:8px; border:none; color:#374151; background:#f3f4f6;">
                    পরে
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var STORAGE_KEY = '{{ $storageKey }}';
    var banner = document.getElementById('ameelhub-pwa-install-banner');
    var installBtn = document.getElementById('ameelhub-pwa-install-btn');
    var dismissBtn = document.getElementById('ameelhub-pwa-dismiss-btn');
    var bannerText = document.getElementById('ameelhub-pwa-banner-text');
    var deferredPrompt = null;

    function alreadyStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
    }

    function dismissedThisSession() {
        try { return sessionStorage.getItem(STORAGE_KEY) === '1'; }
        catch (e) { return false; }
    }

    function markDismissed() {
        try { sessionStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
    }

    // Register service worker, scoped to this panel only.
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker
                .register('{{ asset('sw.js') }}', { scope: '{{ $scope }}' })
                .catch(function (err) { console.warn('AmeelHub SW registration failed:', err); });
        });
    }

    if (alreadyStandalone() || dismissedThisSession()) {
        // Already installed or user dismissed this session — do nothing further.
    } else {
        // Android/Chrome: native install prompt available.
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;
            banner.classList.remove('hidden');
        });

        // iOS Safari never fires beforeinstallprompt — show manual instructions instead.
        var isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        var isSafari = /safari/i.test(window.navigator.userAgent) && !/crios|fxios/i.test(window.navigator.userAgent);
        if (isIos && isSafari) {
            bannerText.textContent = 'নিচের Share বাটনে ট্যাপ করে "Add to Home Screen" নির্বাচন করুন।';
            installBtn.style.display = 'none';
            setTimeout(function () { banner.classList.remove('hidden'); }, 2000);
        }
    }

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        markDismissed();
        banner.classList.add('hidden');
    });

    installBtn.addEventListener('click', function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.finally(function () { deferredPrompt = null; });
        banner.classList.add('hidden');
    });

    dismissBtn.addEventListener('click', function () {
        markDismissed();
        banner.classList.add('hidden');
    });
})();
</script>