<x-filament::section
    :heading="__('filament-breezy::default.profile.passkeys.heading')"
    :description="__('filament-breezy::default.profile.passkeys.description')"
    icon="heroicon-o-key"
    class="shadow-xs border border-gray-200/80 dark:border-white/10"
>
    <div>
        {{ $this->table }}
    </div>
</x-filament::section>

@include('filament-breezy::livewire.passkeys.create-script')

