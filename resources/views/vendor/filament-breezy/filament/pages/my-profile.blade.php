<x-filament::page>
    @php
        $user = filament()->auth()->user();
        $avatarUrl = method_exists($user, 'getFilamentAvatarUrl') ? $user->getFilamentAvatarUrl() : null;
        $registeredComponents = $this->getRegisteredMyProfileComponents();
    @endphp

    {{-- Hero Profile Banner --}}
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                @if($avatarUrl)
                    <img
                        src="{{ $avatarUrl }}"
                        alt="{{ $user->name }}"
                        style="width: 4.5rem; height: 4.5rem; border-radius: 1rem; object-fit: cover; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); flex-shrink: 0; border: 2px solid rgba(245, 158, 11, 0.5);"
                    />
                @else
                    <div style="display: flex; align-items: center; justify-content: center; width: 4.5rem; height: 4.5rem; border-radius: 1rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-size: 1.5rem; font-weight: 700; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); flex-shrink: 0;">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                    </div>
                @endif

                <div style="display: flex; flex-direction: column; gap: 0.375rem;">
                    <div style="display: flex; align-items: center; gap: 0.625rem; flex-wrap: wrap;">
                        <span style="font-size: 1.375rem; font-weight: 700; color: inherit;">
                            {{ $user->name }}
                        </span>
                        @if($user->is_super_admin)
                            <x-filament::badge color="success" icon="heroicon-m-shield-check">
                                Administrador Maestro
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="primary" icon="heroicon-m-user">
                                {{ __('Usuario') }}
                            </x-filament::badge>
                        @endif
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; opacity: 0.85;">
                        <x-filament::icon icon="heroicon-m-envelope" style="width: 1rem; height: 1rem; opacity: 0.7;" />
                        <span>{{ $user->email }}</span>
                    </div>
                    <div style="font-size: 0.75rem; opacity: 0.6;">
                        Miembro desde {{ $user->created_at ? $user->created_at->translatedFormat('d M, Y') : 'recientemente' }}
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.75rem;">
                @if(method_exists($user, 'hasEnabledTwoFactor') && $user->hasEnabledTwoFactor())
                    <x-filament::badge color="success" icon="heroicon-m-shield-check" size="lg">
                        2FA Activado
                    </x-filament::badge>
                @else
                    <x-filament::badge color="warning" icon="heroicon-m-shield-exclamation" size="lg">
                        2FA Recomendado
                    </x-filament::badge>
                @endif
            </div>
        </div>
    </x-filament::section>

    {{-- Grid Layout: Izquierda (Perfil, Ministerios y Permisos) | Derecha (Seguridad) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem; align-items: start; margin-top: 0.5rem;">
        {{-- Columna 1: Información Personal, Ministerios y Permisos --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @if(isset($registeredComponents['personal_info']))
                @livewire($registeredComponents['personal_info'])
            @endif

            @if(isset($registeredComponents['my_ministries']))
                @livewire($registeredComponents['my_ministries'])
            @endif

            @if(isset($registeredComponents['my_permissions']))
                @livewire($registeredComponents['my_permissions'])
            @endif
        </div>

        {{-- Columna 2: Seguridad de la Cuenta --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @if(isset($registeredComponents['update_password']))
                @livewire($registeredComponents['update_password'])
            @endif

            @if(isset($registeredComponents['two_factor_authentication']))
                @livewire($registeredComponents['two_factor_authentication'])
            @endif

            @if(isset($registeredComponents['browser_sessions']))
                @livewire($registeredComponents['browser_sessions'])
            @endif

            @if(isset($registeredComponents['sanctum_tokens']))
                @livewire($registeredComponents['sanctum_tokens'])
            @endif

            @if(isset($registeredComponents['passkeys']))
                @livewire($registeredComponents['passkeys'])
            @endif

            {{-- Otros componentes personalizados --}}
            @foreach ($registeredComponents as $key => $component)
                @if(!in_array($key, ['personal_info', 'my_ministries', 'my_permissions', 'update_password', 'two_factor_authentication', 'browser_sessions', 'sanctum_tokens', 'passkeys']))
                    @unless(is_null($component))
                        @livewire($component)
                    @endunless
                @endif
            @endforeach
        </div>
    </div>
</x-filament::page>
