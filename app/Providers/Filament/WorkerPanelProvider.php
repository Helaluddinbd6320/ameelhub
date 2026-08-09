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
            // Step: new-locales — registered before notification-bell so it
            // renders to the left of it in the topbar (DOM order = visual
            // order in this flex row), including on mobile.
            //
            // BUG FIX (Helal-reported): dropdown opened and immediately
            // closed itself on click. Root cause — this render hook lives
            // inside Filament's page, which is itself a Livewire component.
            // notification-bell polls periodically, and every Livewire
            // re-render/morph cycle was recreating this node, wiping out
            // Alpine's x-data="{ open: false }" state right after the click
            // set it to true. Wrapping in wire:ignore tells Livewire to never
            // touch this subtree on re-render, so Alpine fully owns it and
            // the open state survives polling cycles.
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => Blade::render('<div wire:ignore><x-language-switcher /></div>'),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => Blade::render('@livewire(\'notification-bell\')'),
            )
            // Global announcement ticker (Admin-editable via Settings →
            // ঘোষণা টিকার tab: alert_ticker_enabled / alert_ticker_message /
            // alert_ticker_whatsapp). Registered BEFORE verify-email-banner
            // so it renders above it in every page's content area.
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn(): string => Blade::render("@include('partials.alert-ticker')"),
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
                'view_site' => MenuItem::make()
                    ->label('সাইট দেখুন')
                    ->icon('heroicon-o-eye')
                    ->url(fn () => url('/'), shouldOpenInNewTab: true),
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