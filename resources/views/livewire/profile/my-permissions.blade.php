<x-filament::section
    heading="Mis Permisos y Accesos"
    description="Módulos y acciones (subpermisos) del panel administrativo a los que tu cuenta tiene acceso."
    icon="heroicon-o-shield-check"
    icon-color="success"
>
    <div style="display: flex; flex-direction: column; gap: 0.875rem;">
        @if($isSuperAdmin)
            <div>
                <x-filament::badge color="success" icon="heroicon-m-shield-check" size="lg">
                    Administrador Maestro · Acceso Total
                </x-filament::badge>
            </div>
        @endif

        @if(count($assignedModules) === 0)
            <div style="padding: 1rem; border-radius: 0.75rem; border: 1px dashed rgba(156, 163, 175, 0.35); text-align: center; font-size: 0.875rem; opacity: 0.8;">
                No tienes módulos administrativos asignados actualmente.
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach($assignedModules as $module)
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; padding: 0.625rem 0.875rem; border-radius: 0.625rem; border: 1px solid rgba(156, 163, 175, 0.18); background: rgba(156, 163, 175, 0.04);">
                        <span style="font-weight: 600; font-size: 0.875rem;">
                            {{ $module['label'] }}
                        </span>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                            @foreach($module['actions'] as $actionLabel)
                                <x-filament::badge color="gray" size="sm">
                                    {{ $actionLabel }}
                                </x-filament::badge>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament::section>
