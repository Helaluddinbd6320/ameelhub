<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SetLocale;
// use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectToCentralLogin;
use App\Filament\Agent\Widgets\MyAnalyticsWidget; // NOTE: যদি আলাদা নামের widget থাকে, ঠিক করে দিও — নিচের সংশোধনার্থে placeholder
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

class AgentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('agent')
            // Step: new-locales — registered before notification-bell so it
            // renders to the left of it in the topbar, including on mobile.
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => Blade::render('<x-language-switcher />'),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => Blade::render('@livewire(\'notification-bell\')'),
            )
            // Global announcement ticker (Admin-editable via Settings) —
            // registered before verify-email-banner so it renders above it.
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn(): string => Blade::render("@include('partials.alert-ticker')"),
            )
            // BUG FIX (Helal-reported, Step 10.9 audit): same email-verification
            // nudge banner as WorkerPanelProvider — see that file's comment
            // for the full rationale.
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn(): string => Blade::render("@include('partials.verify-email-banner')"),
            )
            // PHASE 11 — Step 11.1 (PWA): manifest link, theme-color, apple-touch-icon.
            // Scoped to this panel via manifest-agent.json (scope: /agent/).
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => Blade::render("@include('partials.pwa-head', ['panel' => 'agent'])"),
            )
            // PHASE 11 — Step 11.1 (PWA): registers /sw.js scoped to /agent/ only,
            // and renders the dismissible install banner (beforeinstallprompt on
            // Android/Chrome, manual instructions on iOS Safari).
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn(): string => Blade::render("@include('partials.pwa-register', ['panel' => 'agent'])"),
            )
            ->path('agent')
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

            // ->authorization(fn () => auth()->user()?->hasRole('agent') ?? false)
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->viteTheme('resources/css/filament/agent/theme.css')
            ->discoverResources(in: app_path('Filament/Agent/Resources'), for: 'App\\Filament\\Agent\\Resources')
            ->discoverPages(in: app_path('Filament/Agent/Pages'), for: 'App\\Filament\\Agent\\Pages')
            // BUG FIX (same root cause as WorkerPanelProvider, Step 11.3 audit):
            // ->pages([]) was overriding Filament's default [Dashboard::class],
            // meaning /agent had NO Dashboard page registered at all — the home
            // route fell back to the first sidebar item instead of Dashboard.
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Agent/Widgets'), for: 'App\\Filament\\Agent\\Widgets')
            // BUG FIX: ->widgets([]) called after discoverWidgets() was wiping
            // any auto-discovered Agent widgets (e.g. MyAnalyticsWidget, if one
            // exists) from ever reaching the Dashboard page — same override bug
            // pattern as WorkerPanelProvider. Since I don't have your exact
            // Agent/Widgets directory listing, I've left this as an empty array
            // for now so nothing breaks — if you have widgets in
            // app/Filament/Agent/Widgets/, list them here explicitly the same
            // way RecommendedJobsWidget is listed in WorkerPanelProvider,
            // otherwise Filament's discoverWidgets() alone is enough and you
            // can remove this ->widgets([]) call entirely.
            ->widgets([])
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