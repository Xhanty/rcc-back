@php
    /** @var \App\Models\Poll|null $poll */
    $poll = $poll ?? ($getRecord ? $getRecord() : null);

    if ($poll) {
        $poll->loadCount('votes');
        $poll->loadMissing([
            'options' => fn ($q) => $q->withCount('votes')->orderBy('sort_order'),
            'votes' => fn ($q) => $q->with('option')->whereNotNull('document')->latest(),
        ]);
        $totalVotes = $poll->requires_registration
            ? (int) $poll->votes_count
            : (int) $poll->options->sum('votes_count');
        $registeredVotes = $poll->votes->whereNotNull('document');
    } else {
        $totalVotes = 0;
        $registeredVotes = collect();
    }
@endphp

@if($poll)
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid rgba(156, 163, 175, 0.2);">
            <div>
                @if($poll->tag)
                    <span style="display: inline-block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.15rem 0.55rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; margin-bottom: 0.25rem;">
                        {{ $poll->tag }}
                    </span>
                @endif
                <div style="font-size: 0.95rem; font-weight: 700;">
                    {{ $poll->question }}
                </div>
            </div>
            <div style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.35rem 0.75rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-size: 0.8125rem; font-weight: 700;">
                <span>Total:</span>
                @if($poll->requires_registration)
                    <span>{{ $totalVotes }} {{ $totalVotes === 1 ? 'inscrito' : 'inscritos' }}</span>
                @else
                    <span>{{ $totalVotes }} {{ $totalVotes === 1 ? 'voto' : 'votos' }}</span>
                @endif
            </div>
        </div>

        @if(! $poll->requires_registration)
            @if($poll->options->isEmpty())
                <p style="font-size: 0.875rem; opacity: 0.7; text-align: center; padding: 1rem 0;">
                    Esta encuesta aún no tiene opciones configuradas.
                </p>
            @else
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($poll->options as $option)
                        @php
                            $count = (int) $option->votes_count;
                            $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0;
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; font-size: 0.875rem;">
                                <span style="font-weight: 500;">{{ $option->label }}</span>
                                <span style="font-weight: 700; white-space: nowrap; color: #f59e0b;">
                                    {{ $count }} {{ $count === 1 ? 'voto' : 'votos' }}
                                    <span style="opacity: 0.7; font-weight: 500;">({{ $percentage }}%)</span>
                                </span>
                            </div>
                            <div style="width: 100%; height: 0.55rem; border-radius: 9999px; background: rgba(156, 163, 175, 0.2); overflow: hidden;">
                                <div style="width: {{ $percentage }}%; height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #f59e0b, #d97706); transition: width 0.3s ease;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        @if($poll->requires_registration || $registeredVotes->isNotEmpty())
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.875rem; font-weight: 700;">
                        Personas Inscritas / Confirmadas ({{ $registeredVotes->count() }})
                    </span>
                </div>

                @if($registeredVotes->isEmpty())
                    <p style="font-size: 0.8125rem; opacity: 0.7; text-align: center; padding: 0.75rem 0;">
                        Aún no hay personas inscritas con documento en esta actividad.
                    </p>
                @else
                    <div style="overflow-x: auto; border-radius: 0.75rem; border: 1px solid rgba(156, 163, 175, 0.2);">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.8125rem; text-align: left;">
                            <thead>
                                <tr style="background: rgba(156, 163, 175, 0.08); border-bottom: 1px solid rgba(156, 163, 175, 0.2);">
                                    <th style="padding: 0.6rem 0.75rem; font-weight: 600;">Documento</th>
                                    <th style="padding: 0.6rem 0.75rem; font-weight: 600;">Nombre</th>
                                    <th style="padding: 0.6rem 0.75rem; font-weight: 600;">Correo / Teléfono</th>
                                    <th style="padding: 0.6rem 0.75rem; font-weight: 600;">Habeas Data (Ley 1581)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registeredVotes as $reg)
                                    <tr style="border-bottom: 1px solid rgba(156, 163, 175, 0.12);">
                                        <td style="padding: 0.6rem 0.75rem; font-weight: 600;">{{ $reg->document }}</td>
                                        <td style="padding: 0.6rem 0.75rem;">{{ $reg->name }}</td>
                                        <td style="padding: 0.6rem 0.75rem;">
                                            <div>{{ $reg->email ?: '—' }}</div>
                                            <div style="opacity: 0.75; font-size: 0.75rem;">{{ $reg->phone ?: '—' }}</div>
                                        </td>
                                        <td style="padding: 0.6rem 0.75rem;">
                                            @if($reg->accepted_terms)
                                                <div style="color: #10b981; font-weight: 600; font-size: 0.75rem;">
                                                    ✅ Autorizó ({{ $reg->terms_version ?: '2026-v1' }})
                                                </div>
                                                <div style="opacity: 0.7; font-size: 0.7rem;">
                                                    {{ $reg->terms_accepted_at?->format('d/m/Y H:i') }} · IP: {{ $reg->consent_ip ?: 'N/A' }}
                                                </div>
                                            @else
                                                <span style="opacity: 0.6; font-size: 0.75rem;">Sin autorización</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
