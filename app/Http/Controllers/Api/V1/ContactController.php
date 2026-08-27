<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Contact;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'subject.required' => 'El asunto es obligatorio.',
            'message.required' => 'El mensaje es obligatorio.',
        ]);

        $contact = Contact::create($validated);

        return $this->successResponse(
            $contact,
            'Mensaje de contacto registrado con éxito.',
            201
        );
    }
}
