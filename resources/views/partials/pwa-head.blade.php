@php
    $isAgent = ($panel ?? 'worker') === 'agent';
    $manifest = $isAgent ? asset('manifest-agent.json') : asset('manifest-worker.json');
    $themeColor = $isAgent ? '#00845A' : '#F54A00';
    $icon192 = asset('icons/icon-' . ($isAgent ? 'agent' : 'worker') . '-192.png');
    $appTitle = $isAgent ? 'AmeelHub Agent' : 'AmeelHub Worker';
@endphp
<link rel="manifest" href="{{ $manifest }}">
<meta name="theme-color" content="{{ $themeColor }}">
<link rel="apple-touch-icon" href="{{ $icon192 }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ $appTitle }}">