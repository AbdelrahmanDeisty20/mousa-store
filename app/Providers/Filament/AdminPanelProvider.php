<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Support\StorefrontBranding;
use App\Filament\Widgets\MonthlySalesChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\WeeklyOrdersChart;
use App\Http\Middleware\SetAdminLocale;
use Filament\Actions\Action;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('موسى ستور | Mousa Store')
            ->brandLogo(asset('mainlogo.png'))
            ->darkModeBrandLogo(asset('mainlogo-dark.png'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('mainlogo.png'))
            ->font(
                'Alexandria',
                url: 'https://fonts.googleapis.com/css2?family=Alexandria:wght@300;400;500;600;700;800;900&display=swap',
                provider: GoogleFontProvider::class,
            )
            ->login(Login::class)
            ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::hex('#D9141E'),
                'danger' => Color::hex('#DC2626'),
                'success' => Color::hex('#10B981'),
                'gray' => Color::Zinc,
            ])
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverview::class,
                WeeklyOrdersChart::class,
                MonthlySalesChart::class,
                TopProductsWidget::class,
            ])
            ->userMenuItems([
                Action::make('switchLanguage')
                    ->label(fn (): string => app()->getLocale() === 'ar' ? 'English' : 'العربية')
                    ->icon(Heroicon::OutlinedLanguage)
                    ->url(fn (): string => route('filament.admin.switch-lang', ['lang' => app()->getLocale() === 'ar' ? 'en' : 'ar'])),
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => StorefrontBranding::headMeta())
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn () => StorefrontBranding::themeStyles())
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => StorefrontBranding::topbarLanguageSwitch())
            ->renderHook(PanelsRenderHook::SIMPLE_LAYOUT_START, fn () => StorefrontBranding::simpleTopbar())
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn () => StorefrontBranding::loginTagline())
            ->renderHook(PanelsRenderHook::FOOTER, fn () => StorefrontBranding::simpleFooter(), scopes: Login::class)
            ->routes(function (): void {
                Route::get('/switch-lang/{lang}', function (string $lang) {
                    if (in_array($lang, ['ar', 'en'], true)) {
                        session(['lang' => $lang]);
                        session()->save();
                        App::setLocale($lang);
                    }

                    return redirect()->back(fallback: url('/admin'));
                })->name('switch-lang');
            })
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetAdminLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
