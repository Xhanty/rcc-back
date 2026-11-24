<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Configuration\SocialMediaItemResource;
use App\Models\SocialMediaItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    use ApiResponse;

    /**
     * Devuelve los enlaces y contenidos embebidos de redes sociales y medios digitales.
     * Soporta filtros opcionales: ?placement=community | ?placement=footer | ?platform=youtube|spotify|instagram|facebook
     */
    public function index(Request $request): JsonResponse
    {
        $query = SocialMediaItem::query()
            ->active()
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if (filled($platform = $request->query('platform'))) {
            $query->where('platform', strtolower((string) $platform));
        }

        $placement = strtolower((string) $request->query('placement', ''));
        if ($placement === 'community') {
            $query->where('show_in_community', true);
        } elseif ($placement === 'footer') {
            $query->where('show_in_footer', true);
        }

        $items = $query->get();

        return $this->successResponse([
            'community' => SocialMediaItemResource::collection($items->where('show_in_community', true)->values()),
            'footer' => SocialMediaItemResource::collection($items->where('show_in_footer', true)->values()),
            'items' => SocialMediaItemResource::collection($items),
        ]);
    }
}
