<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Configuration\BannerResource;
use App\Models\Banner;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ApiResponse;

    /**
     * Lista banners activos del frontend (opcionalmente filtrados por ?section=home).
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSections = Banner::frontendSections();
        $section = $request->query('section');

        $query = Banner::query()
            ->active()
            ->whereIn('section', $allowedSections)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC');

        if (filled($section)) {
            if (! in_array($section, $allowedSections, true)) {
                return $this->errorResponse('Sección de banner no válida.', 422);
            }

            $query->forSection($section);
        }

        return $this->successResponse(
            BannerResource::collection($query->get())
        );
    }

    /**
     * Lista banners activos para una sección específica del frontend (ej. /banners/home).
     */
    public function bySection(string $section): JsonResponse
    {
        if (! in_array($section, Banner::frontendSections(), true)) {
            return $this->errorResponse('Sección de banner no válida.', 404);
        }

        $banners = Banner::query()
            ->active()
            ->forSection($section)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->get();

        return $this->successResponse(
            BannerResource::collection($banners)
        );
    }
}
