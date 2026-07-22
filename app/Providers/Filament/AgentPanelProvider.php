<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SetLocale;
// use Filament\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectToCentralLogin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => Blade::render('@livewire(\'notification-bell\')'),
            )
            // BUG FIX (Helal-reported, Step 10.9 audit): same email-verification
            // nudge banner as WorkerPanelProvider — see that file's comment
            // for the full rationale.
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn(): string => Blade::render("@include('partials.verify-email-banner')"),
            )
            ->path('agent')
            ->login()
            ->authGuard('web')
            ->registration(false)
            ->userMenuItems([
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
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Agent/Widgets'), for: 'App\\Filament\\Agent\\Widgets')
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