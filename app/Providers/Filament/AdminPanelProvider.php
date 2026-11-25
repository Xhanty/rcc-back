<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\View\PanelsRenderHook;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Muazzam\SlickScrollbar\SlickScrollbarPlugin;
use App\Filament\ImageProviders\AdminLoginBannerProvider;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->font('Inter')
            ->colors([
                'primary' => [
                    50 => '#eef8f8',
                    100 => '#d5eeee',
                    200 => '#aedede',
                    300 => '#7ac5c7',
                    400 => '#47a3a7',
                    500 => '#2a8286',
                    600 => '#1B4D4F',
                    700 => '#174143',
                    800 => '#153638',
                    900 => '#132e30',
                    950 => '#091b1c',
                ],
                'warning' => Color::hex('#D97706'),
                'success' => Color::hex('#10B981'),
                'info' => Color::hex('#2A8286'),
                'danger' => Color::hex('#DC2626'),
                'gray' => Color::Zinc,
            ])
            ->brandLogo(new \Illuminate\Support\HtmlString('
                <div style="display: flex; flex-direction: row; align-items: center; gap: 0.625rem; height: 2.25rem; white-space: nowrap;">
                    <img src="' . asset('icon.png') . '" alt="Logo" style="height: 2.25rem; width: auto; max-height: 100%; flex-shrink: 0; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));" />
                    <div style="display: flex; flex-direction: column; line-height: 1.1;">
                        <span style="font-family: \'Montserrat\', sans-serif; font-weight: 800; font-size: 1.05rem; letter-spacing: -0.02em; color: inherit;" class="text-gray-950 dark:text-white">
                            RESUCITADOS
                        </span>
                        <span style="font-family: \'Inter\', sans-serif; font-weight: 700; font-size: 0.65rem; letter-spacing: 0.12em; color: #D97706;">
                            PANEL ADMINISTRATIVO
                        </span>
                    </div>
                </div>
            '))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('icon.png'))
            ->globalSearch(false)
            ->maxContentWidth(Width::Full)
            ->sidebarFullyCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Administración'),
                NavigationGroup::make('Configuración'),
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn () => view('filament.components.notification-bell')
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.components.event-wizard-styles')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ForcePasswordChange::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                //
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
            ->plugins([
                SlickScrollbarPlugin::make(),
                FilamentBackgroundsPlugin::make()
                    ->imageProvider(
                        AdminLoginBannerProvider::make()
                    ),
                BreezyCore::make()
                    ->myProfile(
                        hasAvatars: true,
                    )
                    ->myProfileComponents([
                        'personal_info' => \App\Livewire\Profile\CustomPersonalInfo::class,
                        'my_ministries' => \App\Livewire\Profile\MyMinistries::class,
                        'my_permissions' => \App\Livewire\Profile\MyPermissions::class,
                    ])
                    ->enableTwoFactorAuthentication()
                    // ->enablePasskeys()
                    // ->enableSanctumTokens()
                    ->enableBrowserSessions(condition: true),
                FilamentApexChartsPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\MustChangePassword::class,
            ]);
    }
}
