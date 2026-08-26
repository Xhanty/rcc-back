<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Models\EventType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Configuration\EventTypeResource;

class EventTypeController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $eventTypes = EventType::query()
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            EventTypeResource::collection($eventTypes)
        );
    }
}
