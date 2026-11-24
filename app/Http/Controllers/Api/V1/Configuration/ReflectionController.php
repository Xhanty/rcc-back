<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Configuration\ReflectionResource;
use App\Models\Reflection;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReflectionController extends Controller
{
    use ApiResponse;

    /**
     * Lista las lecturas y reflexiones publicadas (permite filtrar por ?featured=1, ?category=..., ?limit=...).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Reflection::query()
            ->with('user')
            ->active()
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('category')) {
            $query->where('category', (string) $request->query('category'));
        }

        if ($request->filled('limit')) {
            $limit = max(1, min(50, (int) $request->query('limit')));

            return $this->successResponse(
                ReflectionResource::collection($query->limit($limit)->get())
            );
        }

        return $this->successResponse(
            ReflectionResource::collection($query->get())
        );
    }

    /**
     * Devuelve el detalle completo de una lectura o reflexión por su slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $reflection = Reflection::query()
            ->with('user')
            ->active()
            ->where('slug', $slug)
            ->first();

        if (! $reflection) {
            return $this->errorResponse('Lectura o reflexión no encontrada.', 404);
        }

        return $this->successResponse(ReflectionResource::make($reflection));
    }
}
