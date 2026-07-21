<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Http\Request;

/**
 * তিনটা প্যানেল (admin/agent/worker) আলাদা ->login() ব্যবহার করে, তাই
 * Filament-এর ডিফল্ট Authenticate middleware unauthenticated হলে
 * /admin/login, /agent/login, /worker/login — প্যানেল-নির্দিষ্ট, styling-বিহীন
 * পেজে পাঠাতো। কিন্তু আমাদের একটাই কাস্টম, সুন্দর ডিজাইন করা লগইন পেজ আছে
 * (/login, যেখানে Google/Facebook দিয়ে লগইনও আছে) — যেটা role অনুযায়ী সঠিক
 * প্যানেলে redirect করে (SocialAuthController@callback দেখুন)।
 *
 * তাই এই middleware Filament-এর redirectTo() override করে সবসময়
 * route('login') এ পাঠায়, প্যানেলের নিজস্ব লগইন URL এ না।
 */
class RedirectToCentralLogin extends FilamentAuthenticate
{
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return route('login');
    }
}