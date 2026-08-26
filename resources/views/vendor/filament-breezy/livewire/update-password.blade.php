<form wire:submit.prevent="submit">
    <x-filament::section
        :heading="__('filament-breezy::default.profile.password.heading')"
        :description="__('filament-breezy::default.profile.password.subheading')"
        icon="heroicon-o-key"
        icon-color="primary"
    >
        <div style="padding-top: 0.25rem; padding-bottom: 0.25rem;">
            {{ $this->form }}
        </div>

        <x-slot name="footer">
            <div style="display: flex; justify-content: flex-end; align-items: center;">
                <x-filament::button type="submit" icon="heroicon-m-lock-closed" color="primary">
                    {{ __('filament-breezy::default.profile.password.submit.label') }}
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::section>
</form>


