<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Configuration\MinistryResource;
use App\Models\Ministry;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MinistryController extends Controller
{
    use ApiResponse;

    /**
     * Lista los ministerios activos visibles para la página principal.
     */
    public function index(): JsonResponse
    {
        $ministries = Ministry::query()
            ->with(['leaders:id,name', 'servants:id,name'])
            ->active()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('title', 'ASC')
            ->get();

        return $this->successResponse(
            MinistryResource::collection($ministries)
        );
    }
}
