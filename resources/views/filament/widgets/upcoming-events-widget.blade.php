<x-filament::section>
    <x-slot name="heading">
        <div>
            <div style="font-size: 1rem; font-weight: 700; color: inherit;">
                Próximos Eventos Programados
            </div>
            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.125rem;">
                Calendario inmediato de actividades pastorales
            </div>
        </div>
    </x-slot>

    @php
        $upcomingEvents = $this->getUpcomingEvents();
    @endphp

    @if($upcomingEvents->isEmpty())
        <div style="padding: 2rem; text-align: center; opacity: 0.7;">
            <x-filament::icon icon="heroicon-o-calendar" style="width: 2rem; height: 2rem; margin: 0 auto 0.5rem auto; opacity: 0.5;" />
            <div style="font-size: 0.875rem;">No hay eventos programados en este momento.</div>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($upcomingEvents as $evt)
                @php
                    $start = $evt->start_datetime;
                    $monthStr = strtoupper($start ? $start->translatedFormat('M') : 'S/F');
                    $dayStr = $start ? $start->format('d') : '--';
                    $timeStr = $start ? $start->format('H:i') . ' hrs' : '';
                    $location = $evt->modality === 'virtual' 
                        ? ($evt->live_url ? 'Transmisión Online' : 'Virtual') 
                        : ($evt->venue_name ?? $evt->address ?? 'Presencial');
                @endphp
                <div 
                    style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.625rem 0.75rem; border-radius: 0.625rem; background-color: rgba(156,163,175,0.05); border: 1px solid rgba(156,163,175,0.12); flex-wrap: wrap;"
                >
                    <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0; flex: 1 1 12rem;">
                        {{-- Date Badge Box --}}
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 2.75rem; height: 2.75rem; border-radius: 0.5rem; background-color: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.08); flex-shrink: 0;">
                            <span style="font-size: 0.625rem; font-weight: 700; color: #94a3b8; line-height: 1;">{{ $monthStr }}</span>
                            <span style="font-size: 1.125rem; font-weight: 800; color: #ffffff; line-height: 1.1; margin-top: 0.125rem;">{{ $dayStr }}</span>
                        </div>

                        {{-- Event Info --}}
                        <div style="display: flex; flex-direction: column; min-width: 0; flex: 1;">
                            <div style="font-size: 0.875rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $evt->title }}
                            </div>
                            <div style="font-size: 0.75rem; opacity: 0.65; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $location }} @if($timeStr) • {{ $timeStr }} @endif
                            </div>
                        </div>
                    </div>

                    {{-- Badges --}}
                    <div style="display: flex; align-items: center; gap: 0.375rem; flex-shrink: 0;">
                        <x-filament::badge :color="$evt->modality === 'in_person' ? 'warning' : 'info'" size="sm">
                            {{ $evt->modality === 'in_person' ? 'Presencial' : 'Virtual' }}
                        </x-filament::badge>

                        <x-filament::badge 
                            :color="match($evt->status) {
                                'published' => 'success',
                                'draft' => 'warning',
                                'completed' => 'gray',
                                default => 'gray'
                            }" 
                            size="sm"
                        >
                            {{ match($evt->status) {
                                'published' => 'Publicado',
                                'draft' => 'Borrador',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                                default => 'No publicado'
                            } }}
                        </x-filament::badge>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament::section>
