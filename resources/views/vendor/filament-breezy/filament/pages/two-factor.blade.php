<x-filament-panels::page.simple>
    <form wire:submit="authenticate" class="space-y-6">
        {{ $this->form }}

        <div style="width: 100%;">
            <x-filament::button type="submit" color="primary" style="width: 100%; display: flex; justify-content: center;">
                Confirmar
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page.simple>
