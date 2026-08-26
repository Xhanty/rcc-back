<x-filament::section
    :heading="__('filament-breezy::default.profile.2fa.title')"
    :description="__('filament-breezy::default.profile.2fa.description')"
    icon="heroicon-o-shield-check"
    icon-color="primary"
>
    @if($this->showRequiresTwoFactorAlert())
        <div style="margin-bottom: 1rem;">
            <x-filament::callout color="danger" icon="heroicon-s-shield-exclamation">
                {{ __('filament-breezy::default.profile.2fa.must_enable') }}
            </x-filament::callout>
        </div>
    @endif

    @unless ($user->hasEnabledTwoFactor())
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; border-radius: 0.75rem; background-color: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2);">
                <div style="color: rgb(217, 119, 6); flex-shrink: 0;">
                    <x-filament::icon icon="heroicon-o-shield-exclamation" style="width: 1.5rem; height: 1.5rem;" />
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span style="font-weight: 600; font-size: 0.875rem;">
                        {{ __('filament-breezy::default.profile.2fa.not_enabled.title') }}
                    </span>
                    <span style="font-size: 0.8125rem; opacity: 0.8; line-height: 1.4;">
                        {{ __('filament-breezy::default.profile.2fa.not_enabled.description') }}
                    </span>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div style="display: flex; justify-content: flex-end; align-items: center;">
                {{ $this->enableAction }}
            </div>
        </x-slot>
    @else
        @if ($user->hasConfirmedTwoFactor())
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div style="display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; border-radius: 0.75rem; background-color: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2);">
                    <div style="color: rgb(16, 185, 129); flex-shrink: 0;">
                        <x-filament::icon icon="heroicon-o-shield-check" style="width: 1.5rem; height: 1.5rem;" />
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <span style="font-weight: 600; font-size: 0.875rem;">
                            {{ __('filament-breezy::default.profile.2fa.enabled.title') }}
                        </span>
                        <span style="font-size: 0.8125rem; opacity: 0.8; line-height: 1.4;">
                            {{ __('filament-breezy::default.profile.2fa.enabled.description') }}
                        </span>
                    </div>
                </div>

                @if ($showRecoveryCodes)
                    <div style="padding: 1rem; border-radius: 0.75rem; background-color: rgba(128, 128, 128, 0.05); border: 1px solid rgba(128, 128, 128, 0.15); display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; font-weight: 600;">
                                {{ __('filament-breezy::default.profile.2fa.enabled.store_codes') }}
                            </span>
                            <x-filament-breezy::clipboard-link :data="$this->recoveryCodes->join(',')" />
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; font-family: monospace; font-size: 0.8125rem;">
                            @foreach ($this->recoveryCodes->toArray() as $code)
                                <div style="padding: 0.375rem 0.5rem; border-radius: 0.5rem; text-align: center; font-weight: 600; background-color: rgba(128, 128, 128, 0.1);">
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <x-slot name="footer">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div>{{ $this->regenerateCodesAction }}</div>
                    <div>{{ $this->disableAction }}</div>
                </div>
            </x-slot>
        @else
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div style="display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; border-radius: 0.75rem; background-color: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2);">
                    <div style="color: rgb(217, 119, 6); flex-shrink: 0;">
                        <x-filament::icon icon="heroicon-o-question-mark-circle" style="width: 1.5rem; height: 1.5rem;" />
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <span style="font-weight: 600; font-size: 0.875rem;">
                            {{ __('filament-breezy::default.profile.2fa.finish_enabling.title') }}
                        </span>
                        <span style="font-size: 0.8125rem; opacity: 0.8; line-height: 1.4;">
                            {{ __('filament-breezy::default.profile.2fa.finish_enabling.description') }}
                        </span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; border-radius: 0.75rem; background-color: white; border: 1px solid rgba(128, 128, 128, 0.2); text-align: center; color: #111;">
                        <div style="padding: 0.5rem; background: white; border-radius: 0.5rem; display: inline-block;">
                            {!! $this->getTwoFactorQrCode() !!}
                        </div>
                        <span style="margin-top: 0.5rem; font-size: 0.75rem; color: #666;">
                            {{ __('filament-breezy::default.profile.2fa.setup_key') }}
                        </span>
                        <code style="margin-top: 0.25rem; font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #d97706; background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 0.25rem;">
                            {{ $this->two_factor_secret }}
                        </code>
                    </div>

                    <div style="padding: 1rem; border-radius: 0.75rem; background-color: rgba(128, 128, 128, 0.05); border: 1px solid rgba(128, 128, 128, 0.15); display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.75rem; font-weight: 600;">
                                {{ __('filament-breezy::default.profile.2fa.enabled.store_codes') }}
                            </span>
                            <x-filament-breezy::clipboard-link :data="$this->recoveryCodes->join(',')" />
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.375rem; font-family: monospace; font-size: 0.75rem;">
                            @foreach ($this->recoveryCodes->toArray() as $code)
                                <div style="padding: 0.25rem 0.375rem; border-radius: 0.375rem; text-align: center; font-weight: 600; background-color: rgba(128, 128, 128, 0.1);">
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div>{{ $this->confirmAction }}</div>
                    <div>{{ $this->disableAction }}</div>
                </div>
            </x-slot>
        @endif
    @endunless

    <x-filament-actions::modals />
</x-filament::section>

