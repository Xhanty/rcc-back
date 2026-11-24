<?php

namespace App\Http\Resources\V1\Configuration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $media = [];
        $images = [];
        $videos = [];

        foreach ($this->getImagePaths() as $path) {
            $url = $this->resolveImageUrl($path);
            if (! $url) {
                continue;
            }

            $isVideo = \App\Models\Banner::isVideoPath($path);
            $type = $isVideo ? 'video' : 'image';

            $media[] = [
                'url' => $url,
                'type' => $type,
                'muted' => $isVideo ? true : null,
            ];

            if ($isVideo) {
                $videos[] = $url;
            } else {
                $images[] = $url;
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'section' => $this->section,
            'media_type' => ! empty($videos) ? 'video' : 'image',
            'video' => $videos[0] ?? null,
            'videos' => $videos,
            'image' => $images[0] ?? ($videos[0] ?? null),
            'images' => $images,
            'media' => $media,
            'muted' => true,
            'description' => $this->description,
            'link_url' => $this->link_url,
            'sort_order' => $this->sort_order,
        ];
    }

    protected function resolveImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        if (app()->environment('local')) {
            return 'https://resucitadosrcc.com/storage/' . ltrim($path, '/');
        }

        return Storage::disk('public')->url($path);
    }
}
