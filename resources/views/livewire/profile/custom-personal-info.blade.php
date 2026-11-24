<form wire:submit.prevent="submit">
    <x-filament::section
        heading="Información Personal"
        description="Actualiza tu foto de perfil, datos básicos y tu información de asistente en Resucitados RCC."
        icon="heroicon-o-user"
        icon-color="primary"
    >
        <div style="padding-top: 0.25rem; padding-bottom: 0.25rem;">
            {{ $this->form }}
        </div>

        <x-slot name="footer">
            <div style="display: flex; justify-content: flex-end; align-items: center;">
                <x-filament::button type="submit" icon="heroicon-m-check" color="primary">
                    Guardar cambios
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::section>
</form>
