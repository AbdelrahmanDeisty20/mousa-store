<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function getHeading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getHeading();
        }

        return __('Sign In');
    }

    public function getSubheading(): string | Htmlable | null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getSubheading();
        }

        $subtitle = e(__('Welcome back to Mousa Store Admin'));

        return new HtmlString(
            "<div class=\"ms-login-subheading\">"
            . "<span>{$subtitle}</span>"
            . "<span class=\"ms-emerald-bar\" aria-hidden=\"true\"></span>"
            . "</div>"
        );
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('Email Address'))
            ->placeholder('admin@admin.com')
            ->prefixIcon(Heroicon::OutlinedEnvelope)
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('Password'))
            ->placeholder('••••••••')
            ->prefixIcon(Heroicon::OutlinedLockClosed)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required();
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('Sign In'))
            ->submit('authenticate');
    }
}
