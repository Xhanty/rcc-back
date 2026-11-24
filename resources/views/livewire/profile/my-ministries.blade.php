<x-filament::section
    heading="Mi Rol y Ministerios"
    description="Tu rol dentro del Equipo de Servidores y los ministerios en los que participas actualmente (solo informativo)."
    icon="heroicon-o-user-group"
    icon-color="primary"
>
    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
        @if($communityRole)
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.08);">
                <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.75;">
                        Rol en la Comunidad ({{ $communityRole['role_group_label'] }})
                    </span>
                    <span style="font-weight: 700; font-size: 0.9375rem;">
                        {{ $communityRole['display_title'] }}
                    </span>
                </div>

                <x-filament::badge color="warning" icon="heroicon-m-identification">
                    {{ $communityRole['role_group_label'] }}
                </x-filament::badge>
            </div>
        @endif

        @if(count($ministries) === 0 && ! $communityRole)
            <div style="padding: 1rem; border-radius: 0.75rem; border: 1px dashed rgba(156, 163, 175, 0.35); text-align: center; font-size: 0.875rem; opacity: 0.8;">
                Actualmente no estás vinculado en el Equipo de Servidores ni en ningún ministerio.
            </div>
        @else
            @foreach($ministries as $ministry)
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; padding: 0.875rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(156, 163, 175, 0.2); background: rgba(156, 163, 175, 0.04);">
                    <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                        <span style="font-weight: 700; font-size: 0.9375rem;">
                            {{ $ministry['title'] }}
                        </span>
                        @if($ministry['schedule'] || $ministry['location'])
                            <span style="font-size: 0.8125rem; opacity: 0.75;">
                                {{ implode(' — ', array_filter([$ministry['schedule'], $ministry['location']])) }}
                            </span>
                        @endif
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        @if($ministry['role_type'] === 'leader')
                            <x-filament::badge color="warning" icon="heroicon-m-star">
                                Líder de Ministerio
                            </x-filament::badge>
                        @else
                            <x-filament::badge color="info" icon="heroicon-m-user-group">
                                Servidor
                            </x-filament::badge>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-filament::section>
