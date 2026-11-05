<x-filament-panels::page>
    <div class="rcc-reports-wrapper">

        {{-- ===== REPORTE 1: CUMPLEAÑOS DEL MES ACTUAL ===== --}}
        @php
            $bData = $this->getBirthdaysData();
            $bItems = $bData['items'];
            $bTotal = $bData['total'];
            $bPage  = $bData['currentPage'];
            $bLast  = $bData['lastPage'];
        @endphp

        <div class="rcc-report-card">
            {{-- Header --}}
            <div class="rcc-report-header">
                <div class="rcc-report-header-left">
                    <div class="rcc-report-icon rcc-icon-birthday">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-1.5-.75M9 6.75v.008h.008V6.75H9Zm6 0v.008h.008V6.75H15Zm-3 0v.008h.008V6.75H12Zm-6 4.5v.008h.008v-.008H6Zm12 0v.008h.008v-.008H18Zm-9 3v.008h.008v-.008H9Zm6 0v.008h.008v-.008H15Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="rcc-report-title">Cumpleaños del Mes Actual</h2>
                        <p class="rcc-report-subtitle">
                            {{ $bTotal }} {{ $bTotal === 1 ? 'resucitado cumple' : 'resucitados cumplen' }} años este mes
                        </p>
                    </div>
                </div>
                <a href="{{ route('reports.download.birthdays') }}"
                   class="rcc-btn-download rcc-btn-birthday"
                   title="Descargar reporte en formato Excel (.xlsx)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:1rem;height:1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Descargar XLSX
                </a>
            </div>

            {{-- Table --}}
            @if(count($bItems) === 0)
                <div class="rcc-empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 10.608v2.513" />
                    </svg>
                    <p>No hay cumpleañeros registrados este mes.</p>
                </div>
            @else
                <div class="rcc-table-wrap">
                    <table class="rcc-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Documento</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Cumpleaños</th>
                                <th>Edad que Cumple</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bItems as $i => $row)
                                @php
                                    $num = ($bPage - 1) * $this->perPage + $i + 1;
                                    $dia = $row->dia_cumpleanos ?? '--';
                                    $mes = $row->mes_cumpleanos ?? '--';
                                @endphp
                                <tr>
                                    <td class="rcc-td-num">{{ $num }}</td>
                                    <td class="rcc-td-doc">{{ $row->documento }}</td>
                                    <td class="rcc-td-name">{{ $row->nombre }}</td>
                                    <td class="rcc-td-secondary">{{ $row->correo ?? '—' }}</td>
                                    <td class="rcc-td-secondary">{{ $row->telefono ?? '—' }}</td>
                                    <td>
                                        <span class="rcc-badge rcc-badge-birthday">
                                            {{ $dia }}/{{ $mes }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($row->edad_que_cumple_este_ano)
                                            <span class="rcc-badge rcc-badge-age">{{ $row->edad_que_cumple_este_ano }} años</span>
                                        @else
                                            <span class="rcc-td-secondary">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($bLast > 1)
                    <div class="rcc-pagination">
                        <span class="rcc-pagination-info">
                            Página {{ $bPage }} de {{ $bLast }} &nbsp;·&nbsp; {{ $bTotal }} registros
                        </span>
                        <div class="rcc-pagination-btns">
                            <button wire:click="birthdaysPrevPage" @disabled($bPage <= 1) class="rcc-pag-btn">
                                ← Anterior
                            </button>
                            <button wire:click="birthdaysNextPage" @disabled($bPage >= $bLast) class="rcc-pag-btn">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rcc-pagination">
                        <span class="rcc-pagination-info">{{ $bTotal }} {{ $bTotal === 1 ? 'registro' : 'registros' }}</span>
                    </div>
                @endif
            @endif
        </div>

        {{-- ===== REPORTE 2: SEGUIMIENTO DE PASTOREO ===== --}}
        @php
            $pData = $this->getPastoreoData();
            $pItems = $pData['items'];
            $pTotal = $pData['total'];
            $pPage  = $pData['currentPage'];
            $pLast  = $pData['lastPage'];
        @endphp

        <div class="rcc-report-card">
            {{-- Header --}}
            <div class="rcc-report-header">
                <div class="rcc-report-header-left">
                    <div class="rcc-report-icon rcc-icon-pastoreo">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="rcc-report-title">Seguimiento de Pastoreo</h2>
                        <p class="rcc-report-subtitle">
                            {{ $pTotal }} {{ $pTotal === 1 ? 'resucitado en seguimiento' : 'resucitados en seguimiento' }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('reports.download.pastoreo') }}"
                   class="rcc-btn-download rcc-btn-pastoreo"
                   title="Descargar reporte en formato Excel (.xlsx)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:1rem;height:1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Descargar XLSX
                </a>
            </div>

            {{-- Table --}}
            @if(count($pItems) === 0)
                <div class="rcc-empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584" />
                    </svg>
                    <p>No hay registros de seguimiento disponibles.</p>
                </div>
            @else
                <div class="rcc-table-wrap">
                    <table class="rcc-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Documento</th>
                                <th>Nombre Completo</th>
                                <th>Teléfono</th>
                                <th>Barrio</th>
                                <th>Última Asistencia</th>
                                <th>Días sin Asistir</th>
                                <th>Total Asist.</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pItems as $i => $row)
                                @php
                                    $num = ($pPage - 1) * $this->perPage + $i + 1;
                                    $dias = $row->dias_sin_asistir;
                                    $estado = $row->estado_seguimiento ?? 'Sin datos';
                                    $badgeClass = match(true) {
                                        str_contains(strtolower($estado), 'activo') || str_contains(strtolower($estado), 'reciente') => 'rcc-badge-active',
                                        str_contains(strtolower($estado), 'seguimiento') => 'rcc-badge-warning',
                                        str_contains(strtolower($estado), 'inactivo') || str_contains(strtolower($estado), 'perdido') => 'rcc-badge-danger',
                                        default => 'rcc-badge-neutral',
                                    };
                                    $diasClass = match(true) {
                                        $dias !== null && $dias <= 30   => 'rcc-dias-low',
                                        $dias !== null && $dias <= 90   => 'rcc-dias-mid',
                                        $dias !== null && $dias > 90    => 'rcc-dias-high',
                                        default => '',
                                    };
                                @endphp
                                <tr>
                                    <td class="rcc-td-num">{{ $num }}</td>
                                    <td class="rcc-td-doc">{{ $row->documento }}</td>
                                    <td class="rcc-td-name">{{ $row->nombre }}</td>
                                    <td class="rcc-td-secondary">{{ $row->telefono ?? '—' }}</td>
                                    <td class="rcc-td-secondary">{{ $row->barrio ?? '—' }}</td>
                                    <td class="rcc-td-secondary">
                                        @if($row->ultima_asistencia)
                                            {{ \Carbon\Carbon::parse($row->ultima_asistencia)->format('d/m/Y') }}
                                        @else
                                            <span style="opacity:0.4;">Nunca</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($dias !== null)
                                            <span class="rcc-dias {{ $diasClass }}">{{ $dias }} días</span>
                                        @else
                                            <span class="rcc-td-secondary">—</span>
                                        @endif
                                    </td>
                                    <td class="rcc-td-num">{{ $row->total_asistencias }}</td>
                                    <td>
                                        <span class="rcc-badge {{ $badgeClass }}">{{ $estado }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($pLast > 1)
                    <div class="rcc-pagination">
                        <span class="rcc-pagination-info">
                            Página {{ $pPage }} de {{ $pLast }} &nbsp;·&nbsp; {{ $pTotal }} registros
                        </span>
                        <div class="rcc-pagination-btns">
                            <button wire:click="pastoreoPrevPage" @disabled($pPage <= 1) class="rcc-pag-btn">
                                ← Anterior
                            </button>
                            <button wire:click="pastoreoNextPage" @disabled($pPage >= $pLast) class="rcc-pag-btn">
                                Siguiente →
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rcc-pagination">
                        <span class="rcc-pagination-info">{{ $pTotal }} {{ $pTotal === 1 ? 'registro' : 'registros' }}</span>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ===== ESTILOS ===== --}}
    <style>
        .rcc-reports-wrapper {
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
            padding-bottom: 2rem;
        }

        /* Card */
        .rcc-report-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 1rem;
            overflow: hidden;
        }
        .dark .rcc-report-card {
            background: rgba(255,255,255,0.03);
            border-color: rgba(255,255,255,0.07);
        }
        :root:not(.dark) .rcc-report-card {
            background: #fff;
            border-color: #e5e7eb;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        /* Header */
        .rcc-report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        :root:not(.dark) .rcc-report-header {
            border-bottom-color: #f3f4f6;
        }
        .rcc-report-header-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }
        .rcc-report-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.625rem;
            flex-shrink: 0;
        }
        .rcc-report-icon svg { width: 1.375rem; height: 1.375rem; }
        .rcc-icon-birthday {
            background: linear-gradient(135deg, rgba(251,191,36,0.2) 0%, rgba(245,158,11,0.3) 100%);
            color: #f59e0b;
        }
        .rcc-icon-pastoreo {
            background: linear-gradient(135deg, rgba(99,102,241,0.2) 0%, rgba(139,92,246,0.3) 100%);
            color: #8b5cf6;
        }
        .rcc-report-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.4;
        }
        .rcc-report-subtitle {
            font-size: 0.8rem;
            opacity: 0.6;
            margin: 0.125rem 0 0 0;
        }

        /* Download button */
        .rcc-btn-download {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.15s, transform 0.15s;
            white-space: nowrap;
        }
        .rcc-btn-download:hover { opacity: 0.85; transform: translateY(-1px); }
        .rcc-btn-birthday {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
        }
        .rcc-btn-pastoreo {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: #fff;
        }

        /* Table */
        .rcc-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .rcc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8375rem;
        }
        .rcc-table thead tr {
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        :root:not(.dark) .rcc-table thead tr {
            border-bottom-color: #f3f4f6;
            background: #f9fafb;
        }
        .rcc-table th {
            padding: 0.625rem 1rem;
            text-align: left;
            font-size: 0.7375rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            opacity: 0.55;
            white-space: nowrap;
        }
        .rcc-table tbody tr {
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.12s;
        }
        :root:not(.dark) .rcc-table tbody tr {
            border-bottom-color: #f3f4f6;
        }
        .rcc-table tbody tr:hover { background: rgba(255,255,255,0.04); }
        :root:not(.dark) .rcc-table tbody tr:hover { background: #f9fafb; }
        .rcc-table td {
            padding: 0.625rem 1rem;
            vertical-align: middle;
        }
        .rcc-td-num   { font-size: 0.75rem; opacity: 0.45; text-align: center; }
        .rcc-td-doc   { font-family: monospace; font-size: 0.8rem; opacity: 0.8; }
        .rcc-td-name  { font-weight: 600; }
        .rcc-td-secondary { opacity: 0.6; font-size: 0.8rem; }

        /* Badges */
        .rcc-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .rcc-badge-birthday { background: rgba(251,191,36,0.15); color: #f59e0b; }
        .rcc-badge-age      { background: rgba(99,102,241,0.15); color: #818cf8; }
        .rcc-badge-active   { background: rgba(16,185,129,0.15); color: #10b981; }
        .rcc-badge-warning  { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .rcc-badge-danger   { background: rgba(239,68,68,0.15);  color: #f87171; }
        .rcc-badge-neutral  { background: rgba(156,163,175,0.15); color: #9ca3af; }

        /* Días sin asistir */
        .rcc-dias {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .rcc-dias-low  { background: rgba(16,185,129,0.15); color: #10b981; }
        .rcc-dias-mid  { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .rcc-dias-high { background: rgba(239,68,68,0.15);  color: #f87171; }

        /* Empty state */
        .rcc-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 3rem 1rem;
            opacity: 0.5;
            gap: 0.5rem;
        }
        .rcc-empty-state svg { width: 2.5rem; height: 2.5rem; }
        .rcc-empty-state p { font-size: 0.875rem; margin: 0; }

        /* Pagination */
        .rcc-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0.875rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.06);
            font-size: 0.8rem;
        }
        :root:not(.dark) .rcc-pagination {
            border-top-color: #f3f4f6;
        }
        .rcc-pagination-info { opacity: 0.6; }
        .rcc-pagination-btns { display: flex; gap: 0.5rem; }
        .rcc-pag-btn {
            padding: 0.35rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.78rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: inherit;
            cursor: pointer;
            transition: background 0.12s;
        }
        :root:not(.dark) .rcc-pag-btn {
            border-color: #e5e7eb;
            background: #fff;
        }
        .rcc-pag-btn:hover:not(:disabled) { background: rgba(255,255,255,0.1); }
        :root:not(.dark) .rcc-pag-btn:hover:not(:disabled) { background: #f9fafb; }
        .rcc-pag-btn:disabled { opacity: 0.35; cursor: not-allowed; }

        /* Mobile responsive */
        @media (max-width: 640px) {
            .rcc-report-header { flex-direction: column; align-items: flex-start; }
            .rcc-btn-download { width: 100%; justify-content: center; }
            .rcc-table th, .rcc-table td { padding: 0.5rem 0.625rem; }
            .rcc-pagination { flex-direction: column; align-items: flex-start; }
        }
    </style>
</x-filament-panels::page>
