<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Configuration\PollResource;
use App\Models\Assistant;
use App\Models\Poll;
use App\Models\PollVote;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PollController extends Controller
{
    use ApiResponse;

    /**
     * Lista las encuestas activas y abiertas con sus opciones y conteo.
     * Si el frontend envía X-VOTER-TOKEN (o ?voter_token=...) y/o X-VOTER-FINGERPRINT,
     * indica en cada encuesta si la persona ya votó y por qué opción.
     */
    public function index(Request $request): JsonResponse
    {
        $polls = Poll::query()
            ->open()
            ->withCount('votes')
            ->with(['options' => fn ($query) => $query->withCount('votes')->orderBy('sort_order')])
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->get();

        $voterToken = $this->extractVoterToken($request);
        $fingerprintHash = $this->hashValue($request->header('X-VOTER-FINGERPRINT') ?? $request->query('fingerprint'));
        $ipHash = $this->hashValue($request->ip());

        if ($polls->isNotEmpty() && ($voterToken || $fingerprintHash || $ipHash)) {
            $pollIds = $polls->pluck('id')->all();

            $existingVotes = PollVote::query()
                ->whereIn('poll_id', $pollIds)
                ->where(function ($query) use ($voterToken, $fingerprintHash, $ipHash): void {
                    if ($voterToken) {
                        $query->orWhere('voter_token', $voterToken);
                    }
                    if ($fingerprintHash) {
                        $query->orWhere('fingerprint_hash', $fingerprintHash);
                    }
                    if ($ipHash) {
                        $query->orWhere(function ($q) use ($ipHash): void {
                            $q->where('ip_hash', $ipHash)
                                ->whereHas('poll', fn ($p) => $p->where('restrict_by_ip', true));
                        });
                    }
                })
                ->get()
                ->keyBy('poll_id');

            foreach ($polls as $poll) {
                $vote = $existingVotes->get($poll->id);
                $poll->setAttribute('user_voted_option_id', $vote?->poll_option_id);
                $poll->setAttribute('user_has_participated', $vote !== null);
            }
        }

        return $this->successResponse(
            PollResource::collection($polls)
        );
    }

    /**
     * Busca un asistente por número de documento para prellenar sus datos en encuestas de inscripción (retiros/actividades).
     * Endpoint: GET /api/v1/polls/lookup-assistant?document=123456&poll_id=1
     */
    public function lookupAssistant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document' => ['required', 'string', 'max:50'],
            'poll_id' => ['nullable', 'integer', 'exists:polls,id'],
        ], [
            'document.required' => 'El número de documento es obligatorio.',
        ]);

        $document = trim($validated['document']);

        $assistant = Assistant::query()
            ->where('document', $document)
            ->first();

        $alreadyRegisteredVote = null;
        if (! empty($validated['poll_id'])) {
            $alreadyRegisteredVote = PollVote::query()
                ->where('poll_id', (int) $validated['poll_id'])
                ->where(function ($q) use ($document, $assistant): void {
                    $q->where('document', $document);
                    if ($assistant) {
                        $q->orWhere('assistant_id', $assistant->id);
                    }
                })
                ->first();
        }

        return $this->successResponse([
            'exists' => $assistant !== null,
            'already_registered' => $alreadyRegisteredVote !== null,
            'voted_option_id' => $alreadyRegisteredVote?->poll_option_id,
            'assistant' => $assistant ? [
                'document' => $assistant->document,
                'name' => $assistant->name,
                'email' => $assistant->email,
                'phone' => $assistant->phone,
                'birth_date' => $assistant->birth_date?->format('Y-m-d'),
                'missing_fields' => array_values(array_filter([
                    blank($assistant->name) ? 'name' : null,
                    blank($assistant->email) ? 'email' : null,
                    blank($assistant->phone) ? 'phone' : null,
                    blank($assistant->birth_date) ? 'birth_date' : null,
                ])),
            ] : [
                'document' => $document,
                'name' => null,
                'email' => null,
                'phone' => null,
                'birth_date' => null,
                'missing_fields' => ['name', 'email', 'phone', 'birth_date'],
            ],
        ]);
    }

    /**
     * Registra un voto o inscripción en una encuesta activa.
     * Si la encuesta tiene `requires_registration = true` (o si se envía `document`),
     * valida el documento, crea/actualiza la ficha en `assistants` y guarda la prueba de autorización (Ley 1581).
     */
    public function vote(Request $request, Poll $poll): JsonResponse
    {
        if ($poll->requires_registration || $request->filled('document')) {
            return $this->register($request, $poll);
        }

        if (! $poll->isOpen()) {
            return $this->errorResponse('Esta encuesta ya se encuentra cerrada o inactiva.', 422);
        }

        $validated = $request->validate([
            'option_id' => [
                'required',
                'integer',
                Rule::exists('poll_options', 'id')->where('poll_id', $poll->id),
            ],
            'voter_token' => ['required', 'string', 'min:16', 'max:100'],
            'fingerprint' => ['nullable', 'string', 'max:255'],
        ], [
            'option_id.required' => 'Debes seleccionar una opción para votar.',
            'option_id.exists' => 'La opción seleccionada no pertenece a esta encuesta.',
            'voter_token.required' => 'No se pudo identificar el dispositivo para registrar tu voto.',
        ]);

        $voterToken = trim($validated['voter_token']);
        $fingerprintHash = $this->hashValue($validated['fingerprint'] ?? $request->header('X-VOTER-FINGERPRINT'));
        $ipHash = $this->hashValue($request->ip());

        $alreadyVoted = PollVote::query()
            ->where('poll_id', $poll->id)
            ->where(function ($query) use ($poll, $voterToken, $fingerprintHash, $ipHash): void {
                $query->where('voter_token', $voterToken);

                if ($fingerprintHash) {
                    $query->orWhere('fingerprint_hash', $fingerprintHash);
                }

                if ($poll->restrict_by_ip && $ipHash) {
                    $query->orWhere('ip_hash', $ipHash);
                }
            })
            ->exists();

        if ($alreadyVoted) {
            return $this->errorResponse('Ya registraste tu voto en esta encuesta.', 409);
        }

        try {
            $vote = PollVote::create([
                'poll_id' => $poll->id,
                'poll_option_id' => $validated['option_id'],
                'voter_token' => $voterToken,
                'fingerprint_hash' => $fingerprintHash,
                'ip_hash' => $ipHash,
            ]);
        } catch (QueryException) {
            return $this->errorResponse('Ya registraste tu voto en esta encuesta.', 409);
        }

        $poll->load(['options' => fn ($query) => $query->withCount('votes')->orderBy('sort_order')]);
        $poll->setAttribute('user_voted_option_id', $vote->poll_option_id);

        return $this->successResponse(
            PollResource::make($poll),
            'Tu voto ha sido registrado exitosamente.',
            201
        );
    }

    /**
     * Inscripción / voto con Documento de Identidad + actualización de datos de Asistente + Habeas Data (Ley 1581).
     * Endpoint: POST /api/v1/polls/{poll}/register (también invocado por POST /api/v1/polls/{poll}/vote si requires_registration = true).
     */
    public function register(Request $request, Poll $poll): JsonResponse
    {
        if (! $poll->isOpen()) {
            return $this->errorResponse('Esta encuesta o inscripción ya se encuentra cerrada o inactiva.', 422);
        }

        $hasOptions = $poll->options()->exists();

        $validated = $request->validate([
            'option_id' => [
                (! $poll->requires_registration && $hasOptions) ? 'required' : 'nullable',
                'integer',
                Rule::exists('poll_options', 'id')->where('poll_id', $poll->id),
            ],
            'document' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'accepted_terms' => ['required', 'accepted'],
            'terms_version' => ['nullable', 'string', 'max:20'],
            'voter_token' => ['nullable', 'string', 'max:100'],
            'fingerprint' => ['nullable', 'string', 'max:255'],
        ], [
            'option_id.required' => 'Debes seleccionar una opción.',
            'option_id.exists' => 'La opción seleccionada no pertenece a esta encuesta.',
            'document.required' => 'El número de documento es obligatorio.',
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio para completar tu información.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'phone.required' => 'El número de teléfono / celular es obligatorio.',
            'birth_date.date' => 'La fecha de nacimiento no tiene un formato válido.',
            'accepted_terms.required' => 'Debes aceptar los términos y autorizar el tratamiento de datos personales (Ley 1581 de 2012).',
            'accepted_terms.accepted' => 'Debes aceptar los términos y autorizar el tratamiento de datos personales (Ley 1581 de 2012).',
        ]);

        $document = trim($validated['document']);
        $email = strtolower(trim($validated['email']));
        $name = trim($validated['name']);
        $phone = trim($validated['phone']);
        $birthDate = $validated['birth_date'] ?? null;

        $assistant = Assistant::withTrashed()
            ->where('document', $document)
            ->first();

        // Validar que este documento no se haya registrado ya en esta misma encuesta
        $alreadyRegistered = PollVote::query()
            ->where('poll_id', $poll->id)
            ->where(function ($query) use ($document, $assistant): void {
                $query->where('document', $document);
                if ($assistant) {
                    $query->orWhere('assistant_id', $assistant->id);
                }
            })
            ->exists();

        if ($alreadyRegistered) {
            return $this->errorResponse('Ya existe un registro con este número de documento en esta encuesta/actividad.', 409);
        }

        // Validar que el correo no pertenezca a otro asistente con un documento distinto
        $emailTakenByOther = Assistant::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->when($assistant, fn ($q) => $q->where('id', '!=', $assistant->id))
            ->exists();

        if ($emailTakenByOther) {
            return $this->errorResponse('El correo electrónico ingresado ya está registrado con otro número de documento.', 422);
        }

        $now = now();
        $termsVersion = $validated['terms_version'] ?? '2026-v1';
        $consentIp = $request->ip();
        $consentUserAgent = substr((string) $request->userAgent(), 0, 512);
        $voterToken = filled($validated['voter_token'] ?? null)
            ? trim($validated['voter_token'])
            : 'doc:' . $document;
        $fingerprintHash = $this->hashValue($validated['fingerprint'] ?? $request->header('X-VOTER-FINGERPRINT'));
        $ipHash = $this->hashValue($consentIp);

        try {
            $vote = DB::transaction(function () use (
                $assistant,
                $document,
                $name,
                $email,
                $phone,
                $birthDate,
                $now,
                $termsVersion,
                $consentIp,
                $consentUserAgent,
                $poll,
                $validated,
                $voterToken,
                $fingerprintHash,
                $ipHash
            ): PollVote {
                if ($assistant) {
                    if ($assistant->trashed()) {
                        $assistant->restore();
                    }
                    $assistant->update([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'birth_date' => $birthDate ?: $assistant->birth_date,
                        'accepted_terms' => true,
                        'terms_accepted_at' => $now,
                        'terms_version' => $termsVersion,
                        'consent_ip' => $consentIp,
                        'consent_user_agent' => $consentUserAgent,
                    ]);
                } else {
                    $assistant = Assistant::create([
                        'document' => $document,
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'birth_date' => $birthDate,
                        'accepted_terms' => true,
                        'terms_accepted_at' => $now,
                        'terms_version' => $termsVersion,
                        'consent_ip' => $consentIp,
                        'consent_user_agent' => $consentUserAgent,
                    ]);
                }

                return PollVote::create([
                    'poll_id' => $poll->id,
                    'poll_option_id' => $validated['option_id'] ?? null,
                    'assistant_id' => $assistant->id,
                    'document' => $document,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'birth_date' => $assistant->birth_date?->format('Y-m-d'),
                    'voter_token' => $voterToken,
                    'fingerprint_hash' => $fingerprintHash,
                    'ip_hash' => $ipHash,
                    'accepted_terms' => true,
                    'terms_accepted_at' => $now,
                    'terms_version' => $termsVersion,
                    'consent_ip' => $consentIp,
                    'consent_user_agent' => $consentUserAgent,
                ]);
            });
        } catch (QueryException) {
            return $this->errorResponse('Ya registraste tu participación en esta encuesta/actividad.', 409);
        }

        $poll->loadCount('votes');
        $poll->load(['options' => fn ($query) => $query->withCount('votes')->orderBy('sort_order')]);
        $poll->setAttribute('user_voted_option_id', $vote->poll_option_id);
        $poll->setAttribute('user_has_participated', true);

        return $this->successResponse(
            PollResource::make($poll),
            'Tu registro y datos de asistente han sido guardados exitosamente.',
            201
        );
    }

    protected function extractVoterToken(Request $request): ?string
    {
        $token = $request->header('X-VOTER-TOKEN') ?? $request->query('voter_token');

        if (! is_string($token) || blank(trim($token))) {
            return null;
        }

        return trim($token);
    }

    protected function hashValue(?string $value): ?string
    {
        if (! is_string($value) || blank(trim($value))) {
            return null;
        }

        return hash_hmac('sha256', trim($value), (string) config('app.key'));
    }
}
