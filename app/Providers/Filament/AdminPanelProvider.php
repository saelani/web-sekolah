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
use Filament\Support\Facades\FilamentView;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\BackupManager;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // ->logoutRedirectUrl(url('/'))
            ->darkMode(false)
            ->login()
            ->brandName('Sistem Informasi Sekolah / Dapodik')
            ->viteTheme('resources/css/filament/admin/theme.css')
            /* Warna Akses & Tombol Utama Dapodik */
            ->colors([
                'primary' => Color::Hex('#0284c7'),  // Biru Dapodik
                'info'    => Color::Hex('#003366'),  // Biru Tua Kemendikbud
                'warning' => Color::Hex('#eab308'),  
                'danger'  => Color::Hex('#dc2626'),
                'success' => Color::Hex('#16a34a'),
                'gray'    => Color::Slate,
            ])
            /* Memastikan Sidebar Bisa Di-toggle & Hamburger Icon Aktif */
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                BackupManager::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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

    public function boot(): void
    {
        // 1. INJECT NAMA USER DI SEBELAH AVATAR TOPBAR
        FilamentView::registerRenderHook(
            'panels::user-menu.before',
            fn (): string => '<div class="hidden sm:flex flex-col text-right mr-2 justify-center">
                <span class="text-xs font-bold text-white leading-tight">' . (auth()->user()?->name ?? 'User') . '</span>
                <span class="text-[10px] text-sky-200 leading-tight">' . (auth()->user()?->email ?? '') . '</span>
            </div>'
        );

        // 2. STYLES & FIX IKON SIDEBAR HAMBURGER
        FilamentView::registerRenderHook(
            'panels::styles.after',
            fn (): string => '<style>
                /* =========================================================
                1. TOPBAR UTAMA & HEADER (MENYATU WARNA BIRU DAPODIK)
                ========================================================= */
                header.fi-topbar,
                .fi-topbar-nav,
                .fi-topbar > div { 
                    background-color: #001f3f !important; 
                    border-bottom: 3px solid #0284c7 !important; 
                }

                /* Menampilkan & Memperbaiki Warna Ikon Sidebar Hamburger */
                .fi-topbar button, 
                .fi-topbar svg, 
                .fi-topbar a { 
                    color: #ffffff !important; 
                }

                /* Paksa Tombol Sidebar Toggle Muncul di Mobile & Desktop */
                .fi-topbar-open-sidebar-btn,
                .fi-topbar-close-sidebar-btn,
                button[aria-label*="sidebar"],
                button[aria-label*="Sidebar"] {
                    display: inline-flex !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    color: #ffffff !important;
                }

                /* Avatar / Lingkaran Inisial Nama User */
                .fi-topbar-user-menu button div,
                .fi-avatar {
                    background-color: #0284c7 !important;
                    color: #ffffff !important;
                    border: 2px solid #ffffff !important;
                }

                .fi-topbar-user-menu button {
                    display: flex !important;
                    align-items: center !important;
                }

                /* =========================================================
                2. DROPDOWN USER & MENU LOGOUT (PERBAIKAN WARNA TEKS)
                ========================================================= */
                .fi-dropdown-panel {
                    background-color: #ffffff !important;
                    border: 1px solid #e2e8f0 !important;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2) !important;
                }

                /* Teks & Icon Dalam Dropdown User / Logout */
                .fi-dropdown-list-item,
                .fi-dropdown-list-item *,
                .fi-dropdown-list-item-label,
                .fi-dropdown-header * {
                    color: #1e293b !important; /* Warna gelap agar terbaca jelas di background putih */
                }

                /* Hover pada Menu Dropdown / Logout */
                .fi-dropdown-list-item:hover,
                .fi-dropdown-list-item:hover * {
                    background-color: #f1f5f9 !important;
                    color: #dc2626 !important; /* Merah untuk Logout */
                }

                /* =========================================================
                3. SIDEBAR NAVIGATION FULL GELAP
                ========================================================= */
                aside.fi-sidebar,
                .fi-sidebar-header {
                    background-color: #001f3f !important;
                    border-right: 1px solid #002b5b !important;
                }

                .fi-sidebar-header * {
                    background-color: #001f3f !important;
                    color: #ffffff !important;
                    font-weight: 800 !important;
                }

                .fi-sidebar-group-label,
                .fi-sidebar-item-label,
                .fi-sidebar-item-button,
                .fi-sidebar-item-button * {
                    color: #cbd5e1 !important;
                }

                .fi-sidebar-item-button:hover,
                .fi-sidebar-item-button:hover * {
                    background-color: #002b5b !important;
                    color: #ffffff !important;
                }

                .fi-sidebar-item-active .fi-sidebar-item-button,
                .fi-sidebar-item-active .fi-sidebar-item-button * {
                    background-color: #0284c7 !important;
                    color: #ffffff !important;
                    font-weight: 700 !important;
                    border-radius: 0.5rem !important;
                }

                /* =========================================================
                4. HALAMAN LOGIN & BUTTONS
                ========================================================= */
                .fi-simple-layout {
                    background: linear-gradient(135deg, #001f3f 0%, #003366 50%, #0284c7 100%) !important;
                    min-height: 100vh !important;
                }
                .fi-simple-main {
                    background-color: #ffffff !important;
                    border-radius: 1rem !important;
                    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.4) !important;
                    padding: 2.5rem !important;
                }
                .fi-simple-header-heading { color: #003366 !important; font-weight: 800 !important; }
                .fi-simple-main button[type="submit"] {
                    background: linear-gradient(135deg, #003366 0%, #0284c7 100%) !important;
                    border: none !important;
                    font-weight: 700 !important;
                    color: #ffffff !important;
                }

                body, .fi-body { background-color: #f1f5f9 !important; }
                .fi-btn-primary { background: #0284c7 !important; border: none !important; }
                .fi-btn-primary:hover { background: #0369a1 !important; }
            </style>'
        );
    }
}