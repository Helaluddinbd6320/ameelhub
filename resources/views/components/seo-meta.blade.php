@props(['seo' => []])

@php
    $seo = array_merge([
        'title'       => config('app.name', 'AmeelHub'),
        'description' => '',
        'canonical'   => url()->current(),
        'image'       => asset('images/og-default.jpg'),
        'type'        => 'website',
    ], $seo);
@endphp

<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">

<meta property="og:site_name" content="{{ config('app.name', 'AmeelHub') }}">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
<meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">