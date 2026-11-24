<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $hasContactData = filled($request->input('email')) || filled($request->input('phone'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'accepted_terms' => $hasContactData ? ['required', 'accepted'] : ['nullable', 'boolean'],
            'terms_version' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'subject.required' => 'El asunto es obligatorio.',
            'message.required' => 'El mensaje es obligatorio.',
            'accepted_terms.required' => 'Debes autorizar el tratamiento de datos personales (Ley 1581 de 2012) cuando proporcionas correo o teléfono.',
            'accepted_terms.accepted' => 'Debes autorizar el tratamiento de datos personales (Ley 1581 de 2012) cuando proporcionas correo o teléfono.',
        ]);

        $accepted = (bool) $request->boolean('accepted_terms');
        $validated['accepted_terms'] = $accepted;
        $validated['terms_accepted_at'] = $accepted ? now() : null;
        $validated['terms_version'] = $accepted ? ($request->input('terms_version') ?: '2026-v1') : null;
        $validated['consent_ip'] = $accepted ? $request->ip() : null;
        $validated['consent_user_agent'] = $accepted ? substr((string) $request->userAgent(), 0, 512) : null;

        $contact = Contact::create($validated);

        return $this->successResponse(
            $contact,
            'Mensaje de contacto registrado con éxito.',
            201
        );
    }
}
