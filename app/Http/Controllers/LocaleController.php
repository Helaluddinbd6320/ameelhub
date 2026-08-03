<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Supported application locales.
     *
     * Step: new-locales — kept in sync with SetLocale middleware's
     * $supportedLocales and config('app.available_locales'). If you add
     * another locale in the future, update all three in the same commit.
     *
     * @var array<int, string>
     */
    private const SUPPORTED_LOCALES = ['bn', 'en', 'ar', 'tl', 'hi', 'ur'];

    /**
     * Switch the session locale and redirect back.
     */
    public function __invoke(string $locale, Request $request): RedirectResponse
    {
        if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}