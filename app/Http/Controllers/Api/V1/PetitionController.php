<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Petition;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class PetitionController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'petition' => 'required|string|max:5000',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'petition.required' => 'La petición es obligatoria.',
        ]);

        $petition = Petition::create($validated);

        return $this->successResponse(
            $petition,
            'Petición registrada con éxito.',
            201
        );
    }
}
