<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChange extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected string $view = 'filament.pages.force-password-change';

    protected static ?string $slug = 'force-password-change';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    public ?array $data = [];

    public function hasLogo(): bool
    {
        return true;
    }

    public function hasTopbar(): bool
    {
        return false;
    }

    public function mount(): void
    {
        if (! Auth::check()) {
            redirect()->to(filament()->getLoginUrl());
            return;
        }

        if (! Auth::user()->must_change_password) {
            redirect()->to(filament()->getHomeUrl());
            return;
        }

        $this->form->fill();
    }

    public function getTitle(): string
    {
        return 'Cambio de Contraseña Obligatorio';
    }

    public function getSubheading(): ?string
    {
        return 'Por seguridad, debes establecer tu nueva contraseña personal antes de ingresar al sistema.';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label('Nueva Contraseña')
                    ->prefixIcon(Heroicon::OutlinedLockClosed)
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::default()->min(8))
                    ->placeholder('Mínimo 8 caracteres'),

                TextInput::make('password_confirmation')
                    ->label('Confirmar Nueva Contraseña')
                    ->prefixIcon(Heroicon::OutlinedLockClosed)
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->required()
                    ->placeholder('Repite la nueva contraseña'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSubmitFormAction(),
            $this->getLogoutFormAction(),
        ];
    }

    protected function getSubmitFormAction(): Action
    {
        return Action::make('save')
            ->label('Actualizar Contraseña y Continuar')
            ->submit('save');
    }

    protected function getLogoutFormAction(): Action
    {
        return Action::make('logout')
            ->label('Cerrar Sesión')
            ->color('gray')
            ->action(function () {
                filament()->auth()->logout();
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->to(filament()->getLoginUrl());
            });
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        Notification::make()
            ->title('Contraseña actualizada con éxito')
            ->body('Bienvenido al sistema. Tu nueva contraseña ha sido guardada correctamente.')
            ->success()
            ->send();

        $this->redirect(filament()->getHomeUrl());
    }

    public function logout(): void
    {
        filament()->auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(filament()->getLoginUrl());
    }
}
