@php
    $user = auth()->user();
    $canViewContacts = $user?->hasModuleAccess('contacts') ?? false;
    $canViewPetitions = $user?->hasModuleAccess('petitions') ?? false;
@endphp

@if($canViewContacts || $canViewPetitions)
    @php
        $pendingContactsCount = $canViewContacts
            ? \App\Models\Contact::query()->where('status', 'pending')->count()
            : 0;

        $latestContacts = ($canViewContacts && $pendingContactsCount > 0)
            ? \App\Models\Contact::query()->where('status', 'pending')->latest()->limit(4)->get()
            : collect();

        $pendingPetitionsCount = $canViewPetitions
            ? \App\Models\Petition::query()->where('status', 'pending')->count()
            : 0;

        $latestPetitions = ($canViewPetitions && $pendingPetitionsCount > 0)
            ? \App\Models\Petition::query()->where('status', 'pending')->latest()->limit(4)->get()
            : collect();

        $totalPending = $pendingContactsCount + $pendingPetitionsCount;

        $contactsUrl = $canViewContacts ? \App\Filament\Resources\Administration\ContactResource::getUrl('index') : null;
        $petitionsUrl = $canViewPetitions ? \App\Filament\Resources\Administration\PetitionResource::getUrl('index') : null;
    @endphp

    <style>
        .rcc-notif-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 9999px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #6b7280;
            transition: background-color 0.2s, color 0.2s;
        }
        .rcc-notif-btn:hover {
            background-color: rgba(156, 163, 175, 0.15);
            color: #111827;
        }
        .dark .rcc-notif-btn {
            color: #9ca3af;
        }
        .dark .rcc-notif-btn:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }
        .rcc-notif-panel {
            position: absolute;
            top: calc(100% + 0.6rem);
            right: 0;
            width: 22.5rem;
            max-width: calc(100vw - 1.5rem);
            z-index: 9999;
            border-radius: 0.875rem;
            overflow: hidden;
            background-color: #ffffff !important;
            color: #111827 !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 20px 30px -5px rgba(0, 0, 0, 0.25), 0 10px 15px -6px rgba(0, 0, 0, 0.15) !important;
        }
        .dark .rcc-notif-panel {
            background-color: #18181b !important;
            color: #f9fafb !important;
            border: 1px solid #27272a !important;
            box-shadow: 0 24px 38px -5px rgba(0, 0, 0, 0.75), 0 10px 15px -6px rgba(0, 0, 0, 0.6) !important;
        }
        .rcc-notif-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1rem;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        .dark .rcc-notif-header {
            background-color: #1f1f23;
            border-bottom: 1px solid #27272a;
        }
        .rcc-notif-section-title {
            padding: 0.65rem 1rem 0.35rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background-color: #f3f4f6;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }
        .dark .rcc-notif-section-title {
            background-color: #141417;
            color: #a1a1aa;
            border-bottom: 1px solid #27272a;
        }
        .rcc-notif-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: inherit;
            background-color: #ffffff;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.15s;
        }
        .rcc-notif-item:hover {
            background-color: #f9fafb;
        }
        .dark .rcc-notif-item {
            background-color: #18181b;
            border-bottom: 1px solid #27272a;
        }
        .dark .rcc-notif-item:hover {
            background-color: #232329;
        }
    </style>

    <div
        x-data="{ open: false }"
        @keydown.escape.window="open = false"
        style="position: relative; display: flex; align-items: center; margin-right: 0.75rem;"
    >
        <button
            type="button"
            @click="open = !open"
            class="rcc-notif-btn"
            title="{{ $totalPending > 0 ? $totalPending . ' notificaciones pendientes' : 'Sin notificaciones pendientes' }}"
        >
            <x-filament::icon icon="heroicon-o-bell" style="width: 1.35rem; height: 1.35rem;" />

            @if($totalPending > 0)
                <span
                    style="position: absolute; top: 0.15rem; right: 0.15rem; min-width: 1.1rem; height: 1.1rem; padding: 0 0.25rem; border-radius: 9999px; background-color: #f59e0b; color: #ffffff; font-size: 0.65rem; font-weight: 800; line-height: 1; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.45);"
                >
                    {{ $totalPending > 99 ? '99+' : $totalPending }}
                </span>
            @endif
        </button>

        {{-- Panel Desplegable de Notificaciones --}}
        <div
            x-cloak
            x-show="open"
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="rcc-notif-panel"
        >
            {{-- Cabecera --}}
            <div class="rcc-notif-header">
                <span style="font-size: 0.9rem; font-weight: 700;">
                    Notificaciones
                </span>
                @if($totalPending > 0)
                    <span style="font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 9999px; background-color: rgba(245, 158, 11, 0.18); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.35);">
                        {{ $totalPending }} {{ $totalPending === 1 ? 'pendiente' : 'pendientes' }}
                    </span>
                @else
                    <span style="font-size: 0.72rem; font-weight: 600; opacity: 0.65;">
                        Al día
                    </span>
                @endif
            </div>

            {{-- Cuerpo --}}
            <div style="max-height: 24rem; overflow-y: auto; display: flex; flex-direction: column;">
                @if($totalPending === 0)
                    <div style="padding: 1.75rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                        <x-filament::icon icon="heroicon-o-check-circle" style="width: 2rem; height: 2rem; color: #10b981; opacity: 0.85;" />
                        <div style="font-size: 0.85rem; font-weight: 600;">
                            No tienes notificaciones pendientes
                        </div>
                        <div style="font-size: 0.75rem; opacity: 0.65;">
                            Los nuevos mensajes o peticiones pendientes aparecerán aquí.
                        </div>
                    </div>
                @else
                    {{-- Sección Contactos Pendientes --}}
                    @if($canViewContacts && $pendingContactsCount > 0)
                        <div class="rcc-notif-section-title">
                            <span>Mensajes de Contacto ({{ $pendingContactsCount }})</span>
                            <a href="{{ $contactsUrl }}" style="color: #f59e0b; text-decoration: none; text-transform: none; font-weight: 700;">
                                Ver todos →
                            </a>
                        </div>

                        @foreach($latestContacts as $contact)
                            <a href="{{ $contactsUrl }}" class="rcc-notif-item">
                                <div style="width: 2.1rem; height: 2.1rem; border-radius: 9999px; background-color: rgba(59, 130, 246, 0.18); color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.1rem;">
                                    <x-filament::icon icon="heroicon-m-envelope" style="width: 1.05rem; height: 1.05rem;" />
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem;">
                                        <span style="font-size: 0.825rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $contact->name }}
                                        </span>
                                        <span style="font-size: 0.68rem; opacity: 0.65; white-space: nowrap;">
                                            {{ $contact->created_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.75rem; opacity: 0.8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.1rem;">
                                        {{ $contact->subject ?: $contact->message }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endif

                    {{-- Sección Peticiones Pendientes --}}
                    @if($canViewPetitions && $pendingPetitionsCount > 0)
                        <div class="rcc-notif-section-title">
                            <span>Peticiones de Oración ({{ $pendingPetitionsCount }})</span>
                            <a href="{{ $petitionsUrl }}" style="color: #f59e0b; text-decoration: none; text-transform: none; font-weight: 700;">
                                Ver todas →
                            </a>
                        </div>

                        @foreach($latestPetitions as $petition)
                            <a href="{{ $petitionsUrl }}" class="rcc-notif-item">
                                <div style="width: 2.1rem; height: 2.1rem; border-radius: 9999px; background-color: rgba(244, 63, 94, 0.18); color: #f43f5e; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.1rem;">
                                    <x-filament::icon icon="heroicon-m-heart" style="width: 1.05rem; height: 1.05rem;" />
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem;">
                                        <span style="font-size: 0.825rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $petition->name }}
                                        </span>
                                        <span style="font-size: 0.68rem; opacity: 0.65; white-space: nowrap;">
                                            {{ $petition->created_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div style="font-size: 0.75rem; opacity: 0.8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.1rem;">
                                        {{ \Illuminate\Support\Str::limit($petition->petition, 60) }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif
