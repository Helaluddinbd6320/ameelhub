<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales AmeelHub supports, in priority order.
     */
    protected array $supportedLocales = ['bn', 'en', 'ar'];

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