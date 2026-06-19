<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Angel Krown')
            ->brandLogo(asset('assets/img/logo.png'))
            ->brandLogoHeight('2.6rem')
            ->sidebarCollapsibleOnDesktop()
            ->font('DM Sans')
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarWidth('15rem')
            ->navigationGroups([
                'Salon',
                'WhatsApp',
                'Marketing',
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>'
                    . '@media(min-width:1024px){.fi-main{padding-left:1.75rem!important;padding-right:1.75rem!important;max-width:100%!important}}'
                    // clear separation between sidebar and content (colors only — keep Filament default alignment)
                    . '.fi-sidebar{background:#FBF5EF;border-right:1px solid rgba(58,20,36,.1)}'
                    . '.fi-sidebar .fi-sidebar-header{background:transparent;box-shadow:none;border-bottom:1px solid rgba(58,20,36,.07)}'
                    . '.fi-sidebar-header .fi-logo{margin:0 auto}'
                    . '.fi-main-ctn{background:#fff}'
                    . '.fi-sidebar-group-label{font-size:10px;letter-spacing:.1em;color:rgba(58,20,36,.4);font-weight:600}'
                    . '.fi-sidebar-item-button{font-weight:500}'
                    . '.fi-sidebar-item-button:hover{background:rgba(139,26,79,.06)}'
                    . '.fi-sidebar-item-icon{color:rgba(58,20,36,.45)}'
                    . '.fi-sidebar-item-button[aria-current=page]{background:rgba(139,26,79,.1);color:#8B1A4F;font-weight:600}'
                    . '.fi-sidebar-item-button[aria-current=page] .fi-sidebar-item-icon{color:#8B1A4F}'
                    // modern animated login page
                    . '.fi-simple-layout{position:relative;overflow:hidden;background:#FFF7EF}'
                    . '.fi-simple-layout::before,.fi-simple-layout::after{content:"";position:fixed;border-radius:50%;filter:blur(90px);z-index:0;pointer-events:none}'
                    . '.fi-simple-layout::before{width:46vw;height:46vw;background:radial-gradient(circle,rgba(242,166,196,.5),transparent 70%);top:-12vw;left:-8vw;animation:lgblob1 24s ease-in-out infinite}'
                    . '.fi-simple-layout::after{width:42vw;height:42vw;background:radial-gradient(circle,rgba(201,162,75,.45),transparent 70%);bottom:-14vw;right:-8vw;animation:lgblob2 30s ease-in-out infinite}'
                    . '@keyframes lgblob1{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(7vw,6vh) scale(1.1)}}'
                    . '@keyframes lgblob2{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-6vw,-5vh) scale(1.12)}}'
                    . '.fi-simple-main{position:relative;z-index:1;background:rgba(255,255,255,.82)!important;backdrop-filter:blur(18px);border:1px solid rgba(201,162,75,.28)!important;border-radius:28px!important;box-shadow:0 44px 100px -45px rgba(94,15,54,.45)!important;animation:lgcard .7s cubic-bezier(.19,1,.22,1) both}'
                    . '@keyframes lgcard{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:none}}'
                    . '.fi-simple-layout .fi-logo{height:4.8rem!important;width:auto}'
                    . '.fi-simple-header{gap:.35rem}'
                    . '</style>',
            )
            ->colors([
                'primary' => Color::hex('#8B1A4F'),
                'gold' => Color::hex('#C9A24B'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\RevenueChart::class,
                \App\Filament\Widgets\StatusChart::class,
                \App\Filament\Widgets\TopServices::class,
                \App\Filament\Widgets\LatestBookings::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
