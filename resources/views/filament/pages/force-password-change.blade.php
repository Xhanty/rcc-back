<x-filament-panels::page.simple>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div style="width: 100%; margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <x-filament::button type="submit" color="primary" style="width: 100%; display: flex; justify-content: center;">
                Actualizar Contraseña y Continuar
            </x-filament::button>

            <x-filament::button wire:click="logout" type="button" color="gray" style="width: 100%; display: flex; justify-content: center;">
                Cerrar Sesión
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page.simple>
