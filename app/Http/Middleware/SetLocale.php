<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales AmeelHub supports, in priority order.
     *
     * bn/en/ar were the original set (Bangladeshi + Arabic-speaking employers).
     * tl (Tagalog/Filipino), hi (Hindi), ur (Urdu) added to reach Filipino,
     * Indian, and Pakistani workers. ur is RTL, same as ar — see the main
     * layout's dir/font handling.
     */
    protected array $supportedLocales = ['bn', 'en', 'ar', 'tl', 'hi', 'ur'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        // First-time visitor: no session locale set yet.
        if (! $locale) {
            $locale = config('app.locale', 'bn');
            session(['locale' => $locale]);
        }

        if (! in_array($locale, $this->supportedLocales, true)) {
            $locale = 'bn';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}