<x-filament::section>
    @php
        $stats = $this->getBirthdayStats();
    @endphp

    <x-slot name="heading">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.625rem;">
                <div style="display: flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; background-color: rgba(245,158,11,0.15); color: #f59e0b;">
                    <x-filament::icon icon="heroicon-o-cake" style="width: 1.25rem; height: 1.25rem;" />
                </div>
                <div>
                    <div style="font-size: 1rem; font-weight: 700; color: inherit;">
                        Cumpleaños del Mes
                    </div>
                    <div style="font-size: 0.75rem; opacity: 0.7;">
                        {{ $stats['monthName'] }}
                    </div>
                </div>
            </div>

            <x-filament::badge color="warning" size="sm">
                {{ $stats['total'] }} en total
            </x-filament::badge>
        </div>
    </x-slot>

    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        {{-- 3 Grid Cards --}}
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem;">
            {{-- Hoy --}}
            <div style="padding: 0.875rem 0.25rem; border-radius: 0.625rem; background-color: rgba(156,163,175,0.05); border: 1px solid rgba(156,163,175,0.12); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 0;">
                <div style="font-size: 0.75rem; font-weight: 600; opacity: 0.75; margin-bottom: 0.25rem; white-space: nowrap;">Hoy</div>
                <div style="font-size: 1.375rem; font-weight: 800; color: inherit; line-height: 1;">{{ $stats['today'] }}</div>
                <div style="font-size: 0.6875rem; opacity: 0.6; margin-top: 0.25rem; white-space: nowrap;">festejados</div>
            </div>

            {{-- Esta semana --}}
            <div style="padding: 0.875rem 0.25rem; border-radius: 0.625rem; background-color: rgba(156,163,175,0.05); border: 1px solid rgba(156,163,175,0.12); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 0;">
                <div style="font-size: 0.75rem; font-weight: 600; opacity: 0.75; margin-bottom: 0.25rem; white-space: nowrap;">Esta semana</div>
                <div style="font-size: 1.375rem; font-weight: 800; color: inherit; line-height: 1;">{{ $stats['thisWeek'] }}</div>
                <div style="font-size: 0.6875rem; opacity: 0.6; margin-top: 0.25rem; white-space: nowrap;">próximos</div>
            </div>

            {{-- Resto de mes --}}
            <div style="padding: 0.875rem 0.25rem; border-radius: 0.625rem; background-color: rgba(156,163,175,0.05); border: 1px solid rgba(156,163,175,0.12); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 0;">
                <div style="font-size: 0.75rem; font-weight: 600; opacity: 0.75; margin-bottom: 0.25rem; white-space: nowrap;">Resto de mes</div>
                <div style="font-size: 1.375rem; font-weight: 800; color: inherit; line-height: 1;">{{ $stats['restOfMonth'] }}</div>
                <div style="font-size: 0.6875rem; opacity: 0.6; margin-top: 0.25rem; white-space: nowrap;">agendados</div>
            </div>
        </div>

        {{-- Footer Link --}}
        <div style="text-align: left; padding-top: 0.25rem;">
            <a 
                href="{{ \App\Filament\Pages\Reports::getUrl() }}" 
                style="font-size: 0.8125rem; font-weight: 600; color: #f59e0b; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; transition: opacity 0.2s;"
                onmouseover="this.style.opacity='0.85';"
                onmouseout="this.style.opacity='1';"
            >
                <span>Ver lista completa en Reportes</span>
                <x-filament::icon icon="heroicon-m-chevron-right" style="width: 0.875rem; height: 0.875rem;" />
            </a>
        </div>
    </div>
</x-filament::section>
