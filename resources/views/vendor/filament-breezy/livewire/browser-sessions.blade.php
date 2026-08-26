<x-filament::section
    :heading="__('filament-breezy::default.profile.browser_sessions.heading')"
    :description="__('filament-breezy::default.profile.browser_sessions.subheading')"
    icon="heroicon-o-computer-desktop"
    icon-color="primary"
>
    <div style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
        {{ $this->form }}
    </div>

    <x-filament-actions::modals />
</x-filament::section>


