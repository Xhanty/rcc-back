<?php

namespace App\Http\Resources\V1\Configuration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReflectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $publishedAt = $this->published_at ?? $this->created_at;
        $shortDate = $publishedAt
            ? mb_strtoupper($publishedAt->locale('es')->translatedFormat('d M'))
            : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'reading_time_minutes' => (int) $this->reading_time_minutes,
            'reading_time_label' => "{$this->reading_time_minutes} MIN LECTURA",
            'published_at' => $publishedAt?->format('Y-m-d'),
            'published_date_short' => str_replace('.', '', (string) $shortDate),
            'author' => [
                'name' => $this->display_author_name,
                'initials' => $this->author_initials,
                'avatar_url' => $this->author_avatar_url,
            ],
            'author_name' => $this->display_author_name,
            'author_initials' => $this->author_initials,
            'author_avatar_url' => $this->author_avatar_url,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
