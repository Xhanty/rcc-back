<x-filament-panels::page>
    <style>
        .attendance-events-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
        }
        @media (min-width: 768px) {
            .attendance-events-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .attendance-events-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        .attendance-search-container {
            max-width: 36rem;
            margin: 0 auto;
        }
        .attendance-flex-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3rem;
        }
        .attendance-gap-3 {
            display: flex;
            gap: 0.75rem;
        }
    </style>

    @if (!$selectedEventId)
        {{-- SECTION 1: EVENT SELECTION --}}
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.125rem; font-weight: 700; color: inherit;">Selecciona un Evento</h2>
                <p style="font-size: 0.875rem; opacity: 0.7;">Selecciona el evento para el cual deseas tomar asistencia.</p>
            </div>

            @php $events = $this->getEvents(); @endphp

            @if ($events->isEmpty())
                <x-filament::section>
                    <div class="attendance-flex-center">
                        <x-filament::icon
                            icon="heroicon-o-calendar-days"
                            style="height: 3rem; width: 3rem; opacity: 0.5; margin-bottom: 1rem;"
                        />
                        <h3 style="font-size: 1rem; font-weight: 700;">No hay eventos disponibles</h3>
                        <p style="font-size: 0.875rem; opacity: 0.7; margin-top: 0.25rem; max-width: 28rem;">
                            Solo se pueden tomar asistencias para eventos que estén en estado <strong>Publicado</strong> o <strong>No Publicado</strong>. Crea o publica un evento para comenzar.
                        </p>
                    </div>
                </x-filament::section>
            @else
                <div class="attendance-events-grid">
                    @foreach ($events as $event)
                        <x-filament::section>
                            <x-slot name="heading">
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap;">
                                    <x-filament::badge color="warning">
                                        {{ $event->eventType?->name ?? 'Sin Tipo' }}
                                    </x-filament::badge>
                                    <x-filament::badge :color="$event->modality === 'in_person' ? 'warning' : 'info'">
                                        {{ $event->modality === 'in_person' ? 'Presencial' : 'Virtual' }}
                                    </x-filament::badge>
                                </div>
                                <div style="margin-top: 0.75rem; font-size: 1rem; font-weight: 700; line-height: 1.5rem; min-height: 3rem;">
                                    {{ $event->title }}
                                </div>
                            </x-slot>

                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <p style="font-size: 0.875rem; opacity: 0.7; min-height: 2.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $event->short_description ?? 'Sin descripción corta disponible.' }}
                                </p>
                                
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                    <div style="display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; opacity: 0.7;">
                                        <x-filament::icon icon="heroicon-o-clock" style="height: 1rem; width: 1rem;" />
                                        <span>{{ $event->start_datetime->format('d/m/Y H:i') }}</span>
                                    </div>
                                    
                                    @if($event->modality === 'in_person' && $event->venue_name)
                                        <div style="display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; opacity: 0.7; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <x-filament::icon icon="heroicon-o-map-pin" style="height: 1rem; width: 1rem;" />
                                            <span>{{ $event->venue_name }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div style="margin-top: 0.5rem;">
                                    <x-filament::button
                                        wire:click="selectEvent({{ $event->id }})"
                                        style="width: 100%; justify-content: center;"
                                        color="primary"
                                        icon="heroicon-o-check-circle"
                                    >
                                        Tomar Asistencia
                                    </x-filament::button>
                                </div>
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        {{-- SECTION 2: ATTENDANCE CHECK-IN --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            {{-- EVENT CARD HEADER --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <x-filament::badge color="warning">
                            {{ $selectedEvent->eventType?->name ?? 'Sin Tipo' }}
                        </x-filament::badge>
                        <x-filament::badge :color="$selectedEvent->modality === 'in_person' ? 'warning' : 'info'">
                            {{ $selectedEvent->modality === 'in_person' ? 'Presencial' : 'Virtual' }}
                        </x-filament::badge>
                    </div>
                    <div style="font-size: 1.25rem; font-weight: 700;">
                        {{ $selectedEvent->title }}
                    </div>
                </x-slot>

                <x-slot name="afterHeader">
                    <x-filament::button
                        wire:click="deselectEvent"
                        color="gray"
                        icon="heroicon-o-arrow-left"
                        size="sm"
                    >
                        Regresar
                    </x-filament::button>
                </x-slot>

                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; font-size: 0.875rem; opacity: 0.7;">
                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                        <x-filament::icon icon="heroicon-o-calendar" style="height: 1rem; width: 1rem;" />
                        <span>{{ $selectedEvent->start_datetime->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($selectedEvent->modality === 'in_person' && $selectedEvent->venue_name)
                        <div style="display: flex; align-items: center; gap: 0.375rem;">
                            <x-filament::icon icon="heroicon-o-map-pin" style="height: 1rem; width: 1rem;" />
                            <span>{{ $selectedEvent->venue_name }}</span>
                        </div>
                    @endif
                </div>
            </x-filament::section>

            {{-- SEARCH SECTION --}}
            <div class="attendance-search-container">
                <x-filament::section>
                    <x-slot name="heading">
                        <div style="text-align: center; font-weight: 700;">Registrar Asistencia</div>
                    </x-slot>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <p style="font-size: 0.75rem; opacity: 0.7; text-align: center;">
                            Ingresa el número de documento del asistente para buscarlo o registrarlo en el sistema.
                        </p>

                        <x-filament::input.wrapper>
                            <x-slot name="prefix">
                                <x-filament::icon icon="heroicon-o-identification" style="height: 1.25rem; width: 1.25rem; opacity: 0.5;" />
                            </x-slot>
                            <x-filament::input
                                type="text"
                                wire:model.live.debounce.500ms="searchDocument"
                                placeholder="Cédula / Documento de identidad..."
                                autofocus
                            />
                        </x-filament::input.wrapper>

                        @if(!empty($searchDocument))
                            <div style="border-top: 1px solid rgba(156,163,175,0.15); padding-top: 1rem; margin-top: 0.5rem; text-align: left;">
                                @if($assistantExists && $searchedAssistant)
                                    {{-- PREVIEW ASSISTANT --}}
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <div style="padding: 1rem; background-color: rgba(156,163,175,0.05); border: 1px solid rgba(156,163,175,0.15); border-radius: 0.5rem;">
                                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(156,163,175,0.15); padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                                                <span style="font-size: 0.75rem; font-weight: 600; opacity: 0.6;">Asistente Encontrado</span>
                                                <x-filament::badge color="success" size="sm">Activo</x-filament::badge>
                                            </div>
                                            <div style="display: flex; flex-direction: column; gap: 0.375rem; font-size: 0.875rem;">
                                                <div style="font-weight: 700;">{{ $searchedAssistant->name }}</div>
                                                <div style="font-size: 0.75rem; opacity: 0.7;">Cédula: {{ $searchedAssistant->document }}</div>
                                                <div style="font-size: 0.75rem; opacity: 0.7;">Correo: {{ $searchedAssistant->email ?? 'No registrado' }}</div>
                                                @if($searchedAssistant->phone)
                                                    <div style="font-size: 0.75rem; opacity: 0.7;">Teléfono: {{ $searchedAssistant->phone }}</div>
                                                @endif
                                                <div style="font-size: 0.75rem; opacity: 0.7;">Fecha de Nacimiento: {{ $searchedAssistant->birth_date ? \Carbon\Carbon::parse($searchedAssistant->birth_date)->format('d/m/Y') : 'No registrada' }}</div>
                                            </div>
                                        </div>

                                        @if($hasAttended)
                                            <div style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; background-color: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); color: rgb(217,119,6); border-radius: 0.5rem;">
                                                <x-filament::icon icon="heroicon-o-exclamation-triangle" style="height: 1.25rem; width: 1.25rem; flex-shrink: 0;" />
                                                <span style="font-size: 0.75rem; font-weight: 500;">Este asistente ya ha registrado su asistencia a este evento.</span>
                                            </div>
                                            <div class="attendance-gap-3">
                                                <x-filament::button
                                                    wire:click="cancelSearch"
                                                    color="gray"
                                                    style="width: 100%;"
                                                >
                                                    Limpiar Búsqueda
                                                </x-filament::button>
                                            </div>
                                        @else
                                            <div class="attendance-gap-3">
                                                <x-filament::button
                                                    wire:click="markAttendance"
                                                    color="success"
                                                    icon="heroicon-o-check"
                                                    style="width: 100%; justify-content: center;"
                                                >
                                                    Confirmar Asistencia
                                                </x-filament::button>
                                                <x-filament::button
                                                    wire:click="cancelSearch"
                                                    color="gray"
                                                    style="width: 100%;"
                                                >
                                                    Cancelar
                                                </x-filament::button>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($showRegisterForm)
                                    {{-- REGISTRATION FORM --}}
                                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background-color: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); color: rgb(37,99,235); border-radius: 0.5rem;">
                                            <x-filament::icon icon="heroicon-o-user-plus" style="height: 1.25rem; width: 1.25rem; flex-shrink: 0;" />
                                            <span style="font-size: 0.75rem; font-weight: 500;">El asistente no está registrado en el sistema. Regístralo a continuación.</span>
                                        </div>

                                        <form wire:submit.prevent="registerAndMarkAttendance" style="display: flex; flex-direction: column; gap: 1rem;">
                                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                                <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.8;">Cédula / Documento</label>
                                                <x-filament::input.wrapper>
                                                    <x-filament::input
                                                        type="text"
                                                        wire:model="newAssistantDocument"
                                                        disabled
                                                    />
                                                </x-filament::input.wrapper>
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                                <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.8;">Nombre Completo <span style="color: red;">*</span></label>
                                                <x-filament::input.wrapper :valid="!$errors->has('newAssistantName')">
                                                    <x-filament::input
                                                        type="text"
                                                        wire:model="newAssistantName"
                                                        placeholder="Ej. Juan Pérez"
                                                        required
                                                    />
                                                </x-filament::input.wrapper>
                                                @error('newAssistantName')
                                                    <span style="font-size: 0.75rem; color: red;">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                                <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.8;">Correo Electrónico</label>
                                                <x-filament::input.wrapper :valid="!$errors->has('newAssistantEmail')">
                                                    <x-filament::input
                                                        type="email"
                                                        wire:model="newAssistantEmail"
                                                        placeholder="ejemplo@correo.com"
                                                    />
                                                </x-filament::input.wrapper>
                                                @error('newAssistantEmail')
                                                    <span style="font-size: 0.75rem; color: red;">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                                <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.8;">Teléfono</label>
                                                <x-filament::input.wrapper :valid="!$errors->has('newAssistantPhone')">
                                                    <x-filament::input
                                                        type="text"
                                                        wire:model="newAssistantPhone"
                                                        placeholder="Ej. +57 300 123 4567"
                                                    />
                                                </x-filament::input.wrapper>
                                                @error('newAssistantPhone')
                                                    <span style="font-size: 0.75rem; color: red;">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                                <label style="font-size: 0.75rem; font-weight: 600; opacity: 0.8;">Fecha de Nacimiento <span style="color: red;">*</span></label>
                                                <x-filament::input.wrapper :valid="!$errors->has('newAssistantBirthDate')">
                                                    <x-filament::input
                                                        type="date"
                                                        wire:model="newAssistantBirthDate"
                                                        max="{{ date('Y-m-d') }}"
                                                        required
                                                    />
                                                </x-filament::input.wrapper>
                                                @error('newAssistantBirthDate')
                                                    <span style="font-size: 0.75rem; color: red;">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="attendance-gap-3" style="margin-top: 0.5rem;">
                                                <x-filament::button
                                                    type="submit"
                                                    color="success"
                                                    icon="heroicon-o-check"
                                                    style="width: 100%; justify-content: center;"
                                                >
                                                    Registrar y Asistir
                                                </x-filament::button>
                                                <x-filament::button
                                                    type="button"
                                                    wire:click="cancelSearch"
                                                    color="gray"
                                                    style="width: 100%;"
                                                >
                                                    Cancelar
                                                </x-filament::button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </x-filament::section>
            </div>

            {{-- ATTENDEES LIST SECTION --}}
            <div style="margin-top: 1.5rem;" wire:key="attendees-table-container-{{ $selectedEventId }}">
                <x-filament::section>
                    <x-slot name="heading">
                        Asistentes en este Evento
                    </x-slot>

                    {{ $this->table }}
                </x-filament::section>
            </div>
        </div>
    @endif
</x-filament-panels::page>
