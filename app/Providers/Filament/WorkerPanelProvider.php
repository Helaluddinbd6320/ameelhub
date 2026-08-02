<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SetLocale;
// use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectToCentralLogin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Worker\Widgets\RecommendedJobsWidget;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WorkerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('worker')
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => Blade::render('@livewire(\'notification-bell\')'),
            )
            // BUG FIX (Helal-reported, Step 10.9 audit): email-verification
            // nudge banner. Login/panel access intentionally stays open for
            // unverified users (business decision) — this just renders a
            // persistent reminder + one-click resend at the top of every
            // page's content. Actual blocking happens at the action level
            // (CV submit / Withdrawal / Recharge), not here.
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn(): string => Blade::render("@include('partials.verify-email-banner')"),
            )
            // PHASE 11 — Step 11.1 (PWA): manifest link, theme-color, apple-touch-icon.
            // Scoped to this panel via manifest-worker.json (scope: /worker/).
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => Blade::render("@include('partials.pwa-head', ['panel' => 'worker'])"),
            )
            // PHASE 11 — Step 11.1 (PWA): registers /sw.js scoped to /worker/ only,
            // and renders the dismissible install banner (beforeinstallprompt on
            // Android/Chrome, manual instructions on iOS Safari).
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn(): string => Blade::render("@include('partials.pwa-register', ['panel' => 'worker'])"),
            )
            ->path('worker')
            ->login()
            ->authGuard('web')
            ->registration(false)
            ->userMenuItems([
                'lang_bn' => MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'bn' ? '✓ বাংলা' : 'বাংলা')
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'bn')),
                'lang_en' => MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'en' ? '✓ English' : 'English')
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'en')),
                'lang_ar' => MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'ar' ? '✓ العربية' : 'العربية')
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'ar')),
                'lang_tl' => MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'tl' ? '✓ Tagalog' : 'Tagalog')
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'tl')),
                'lang_hi' => MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'hi' ? '✓ हिन्दी' : 'हिन्दी')
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'hi')),
                'lang_ur' => MenuItem::make()
                    ->label(fn () => app()->getLocale() === 'ur' ? '✓ اردو' : 'اردو')
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'ur')),
                'logout' => MenuItem::make()
                    ->label('লগ আউট')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->url(fn() => route('panel.logout')),
            ])

            // ->authorization(fn () => auth()->user()?->hasRole('worker') ?? false)
            ->colors([
                'primary' => Color::Orange,
            ])
            ->viteTheme('resources/css/filament/worker/theme.css')
            ->discoverResources(in: app_path('Filament/Worker/Resources'), for: 'App\\Filament\\Worker\\Resources')
            ->discoverPages(in: app_path('Filament/Worker/Pages'), for: 'App\\Filament\\Worker\\Pages')
            // BUG FIX (Step 11.3 audit): ->pages([]) was overriding Filament's
            // default [Dashboard::class], meaning /worker had NO Dashboard page
            // registered at all — the home route fell back to the first sidebar
            // item (My Profile) every time, regardless of CV completeness. This
            // is why the "আপনার জন্য সেরা Job" widget never appeared: there was
            // no Dashboard page for it to attach to in the first place.
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Worker/Widgets'), for: 'App\\Filament\\Worker\\Widgets')
            // BUG FIX (Step 11.3 audit): ->widgets([]) called after
            // discoverWidgets() was overriding/wiping the auto-discovered
            // widget set — same override bug as ->pages([]) above. Explicitly
            // listing RecommendedJobsWidget here guarantees it actually
            // reaches the Dashboard page regardless of discovery behavior.
            ->widgets([
                RecommendedJobsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                SanitizeInput::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                // Authenticate::class,
                RedirectToCentralLogin::class,
            ]);
    }
}