<?php

namespace App\Filament\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;

class StorefrontBranding
{
    public static function headMeta(): HtmlString
    {
        $logoUrl = e(asset('mainlogo.png'));

        return new HtmlString(<<<HTML
<meta name="theme-color" content="#D9141E" />
<link rel="apple-touch-icon" href="{$logoUrl}" />
<link rel="shortcut icon" type="image/png" href="{$logoUrl}" />
HTML);
    }

    public static function themeStyles(): HtmlString
    {
        return new HtmlString(<<<'CSS'
<style>
    :root {
        --ms-primary: #D9141E;
        --ms-primary-hover: #b00f17;
        --ms-emerald: #10B981;
        --font-family: 'Alexandria', sans-serif;
    }

    html.fi,
    html.fi body,
    .fi-body {
        font-family: 'Alexandria', sans-serif !important;
    }

    [dir="rtl"] h1,
    [dir="rtl"] h2,
    [dir="rtl"] h3,
    [dir="rtl"] h4,
    [dir="rtl"] .fi-header-heading {
        letter-spacing: 0 !important;
        line-height: 1.5;
    }

    /* Dark Mode Palette synchronized with Mousa Store storefront (#121316 / #1C1D22 / #2E3038) */
    html.dark .fi-body {
        background-color: #121316 !important;
    }

    html.dark .fi-topbar > nav,
    html.dark .fi-sidebar {
        background-color: #121316 !important;
        border-color: #2e3038 !important;
    }

    html.dark .fi-section,
    html.dark .fi-ta-ctn,
    html.dark .fi-wi-stats-overview-stat,
    html.dark .fi-wi-chart,
    html.dark .fi-modal-window {
        background-color: #1c1d22 !important;
        border-color: #2e3038 !important;
    }

    /* Logo switching helper for custom topbar/footer */
    html.dark .ms-logo-light-only {
        display: none !important;
    }

    html:not(.dark) .ms-logo-dark-only {
        display: none !important;
    }

    /* Auth / Login geometric background & storefront card styling */
    .fi-simple-layout {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background-color: #ffffff;
        background-image:
            radial-gradient(rgba(17, 24, 39, 0.06) 1px, transparent 1px),
            linear-gradient(112deg, transparent 0%, rgba(243, 244, 246, 0.9) 28%, rgba(229, 231, 235, 0.45) 56%, transparent 72%);
        background-size: 22px 22px, 100% 100%;
    }

    html.dark .fi-simple-layout {
        background-color: #121316;
        background-image:
            radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px),
            linear-gradient(112deg, transparent 0%, rgba(28, 29, 34, 0.92) 30%, rgba(37, 38, 44, 0.5) 58%, transparent 74%);
        background-size: 22px 22px, 100% 100%;
    }

    .fi-simple-main-ctn {
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .fi-simple-main {
        background-color: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.16) !important;
    }

    html.dark .fi-simple-main {
        background-color: #18191e !important;
        border: 1px solid #2e3038 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.55) !important;
    }

    .ms-login-subheading {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.45rem;
    }

    .ms-emerald-bar {
        display: inline-block;
        width: 2rem;
        height: 0.25rem;
        border-radius: 9999px;
        background-color: #10B981;
    }

    .ms-login-tagline {
        text-align: center;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    html.dark .ms-login-tagline {
        color: #9ca3af;
    }

    .fi-simple-main .fi-btn.fi-color-primary {
        background-color: #D9141E !important;
        box-shadow: 0 10px 18px -4px rgba(217, 20, 30, 0.28);
        font-weight: 700;
        border-radius: 0.5rem;
    }

    .fi-simple-main .fi-btn.fi-color-primary:hover {
        background-color: #b00f17 !important;
    }

    .fi-simple-main .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.28), inset 0 0 0 1px #10B981 !important;
    }

    .ms-auth-topbar {
        width: 100%;
        height: 68px;
        padding: 0 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }

    html.dark .ms-auth-topbar {
        background-color: #121316;
        border-bottom-color: #2e3038;
    }

    .ms-auth-footer {
        width: 100%;
        padding: 1.25rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        background-color: #ffffff;
        border-top: 1px solid #e5e7eb;
        font-size: 0.78rem;
        color: #6b7280;
    }

    html.dark .ms-auth-footer {
        background-color: #0d0e11;
        border-top-color: #2e3038;
        color: #9ca3af;
    }

    .ms-auth-footer-meta {
        width: 100%;
        max-width: 1200px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .ms-auth-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        font-size: 0.78rem;
        font-weight: 600;
        color: #374151;
        background: #f9fafb;
        cursor: pointer;
        text-decoration: none;
    }

    html.dark .ms-auth-action-btn {
        border-color: #2e3038;
        color: #e5e7eb;
        background: #1c1d22;
    }
</style>
CSS);
    }

    public static function topbarLanguageSwitch(): HtmlString
    {
        $target = App::getLocale() === 'ar' ? 'en' : 'ar';
        $label = $target === 'en' ? 'English' : 'العربية';
        $url = e(route('filament.admin.switch-lang', ['lang' => $target]));

        return new HtmlString(
            "<a href=\"{$url}\" class=\"ms-auth-action-btn\" title=\"Switch Language\">{$label}</a>"
        );
    }

    public static function simpleTopbar(): HtmlString
    {
        $lightLogo = e(asset('mainlogo.png'));
        $darkLogo = e(asset('mainlogo-dark.png'));
        $homeUrl = e(url('/'));
        $target = App::getLocale() === 'ar' ? 'en' : 'ar';
        $langLabel = $target === 'en' ? 'English' : 'العربية';
        $langUrl = e(route('filament.admin.switch-lang', ['lang' => $target]));
        $themeLabel = e(__('Theme'));

        return new HtmlString(<<<HTML
<header class="ms-auth-topbar">
    <a href="{$homeUrl}" style="display:inline-flex;align-items:center;">
        <img src="{$lightLogo}" alt="Mousa Store" class="ms-logo-light-only" style="height:44px;width:auto;object-fit:contain;" />
        <img src="{$darkLogo}" alt="Mousa Store" class="ms-logo-dark-only" style="height:44px;width:auto;object-fit:contain;" />
    </a>
    <div style="display:inline-flex;align-items:center;gap:0.5rem;">
        <button
            type="button"
            class="ms-auth-action-btn"
            onclick="(function(){ const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark'; localStorage.setItem('filament:theme:mode', next); document.documentElement.classList.toggle('dark', next === 'dark'); window.theme = next; })()"
        >
            🌓 {$themeLabel}
        </button>
        <a href="{$langUrl}" class="ms-auth-action-btn">{$langLabel}</a>
    </div>
</header>
HTML);
    }

    public static function loginTagline(): HtmlString
    {
        return new HtmlString('<div class="ms-login-tagline">SPORTSWEAR &amp; EQUIPMENT</div>');
    }

    public static function simpleFooter(): HtmlString
    {
        $lightLogo = e(asset('mainlogo.png'));
        $darkLogo = e(asset('mainlogo-dark.png'));
        $copyright = App::getLocale() === 'ar'
            ? '© 2026 جميع الحقوق محفوظة لمتجر موسى ستور'
            : '© 2026 Mousa Store — All rights reserved';
        $poweredBy = App::getLocale() === 'ar'
            ? 'صُمم ونُفذ بواسطة'
            : 'Designed and Developed by';

        return new HtmlString(<<<HTML
<footer class="ms-auth-footer">
    <img src="{$lightLogo}" alt="Mousa Store" class="ms-logo-light-only" style="height:40px;width:auto;object-fit:contain;" />
    <img src="{$darkLogo}" alt="Mousa Store" class="ms-logo-dark-only" style="height:40px;width:auto;object-fit:contain;" />
    <div class="ms-auth-footer-meta">
        <span>{$copyright}</span>
        <span>{$poweredBy} <a href="https://fourthpyramidagcy.com/" target="_blank" rel="noopener" style="color:#D9141E;font-weight:700;text-decoration:none;">Fourth Pyramid Agency</a></span>
    </div>
</footer>
HTML);
    }
}
