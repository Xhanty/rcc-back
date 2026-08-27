<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use Carbon\Carbon;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\V1\Configuration\EventResource;

class EventController extends Controller
{
    use ApiResponse;

    public function eventsHome(): JsonResponse
    {
        $events = Event::query()
            ->with('eventType')
            ->where('is_featured', true)
            ->where('status', 'published')
            ->whereDate('start_datetime', '>=', Carbon::now()->toDateString())
            ->orderBy('start_datetime', 'ASC')
            ->limit(3)
            ->get();

        return $this->successResponse(
            EventResource::collection($events)
        );
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $event = Event::query()
            ->with('eventType')
            ->where('slug', $slug)
            ->first();

        if (! $event) {
            return $this->errorResponse('Evento no encontrado.', 404);
        }

        return $this->successResponse(
            EventResource::make($event)
        );
    }

    public function filter(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ], [
            'year.required' => 'El año es obligatorio.',
            'year.integer' => 'El año debe ser un número entero.',
            'month.required' => 'El mes es obligatorio.',
            'month.integer' => 'El mes debe ser un número entero.',
            'month.min' => 'El mes debe estar entre 1 y 12.',
            'month.max' => 'El mes debe estar entre 1 y 12.',
        ]);

        $year = $request->query('year');
        $month = $request->query('month');

        $events = Event::query()
            ->with('eventType')
            ->whereYear('start_datetime', $year)
            ->whereMonth('start_datetime', $month)
            ->where('status', 'published')
            ->orderBy('start_datetime', 'ASC')
            ->get();

        return $this->successResponse(
            EventResource::collection($events)
        );
    }

    public function getActiveMonths(): JsonResponse
    {
        $rawData = Event::query()
            ->selectRaw('YEAR(start_datetime) as year, MONTH(start_datetime) as month')
            ->where('status', 'published')
            ->groupBy('year', 'month')
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->get();

        $grouped = $rawData->groupBy('year')->map(function ($items, $year) {
            return [
                'year' => (int) $year,
                'months' => $items->map(fn($item) => (int) $item->month)->values()->toArray(),
            ];
        })->values()->toArray();

        return $this->successResponse($grouped);
    }
}
