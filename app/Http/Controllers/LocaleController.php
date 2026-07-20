<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Supported application locales.
     *
     * @var array<int, string>
     */
    private const SUPPORTED_LOCALES = ['bn', 'en', 'ar'];

    /**
     * Switch the session locale (bn/en/ar) and redirect back.
     */
    public function __invoke(string $locale, Request $request): RedirectResponse
    {
        if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}