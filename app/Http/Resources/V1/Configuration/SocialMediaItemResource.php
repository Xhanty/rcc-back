<?php

namespace App\Http\Resources\V1\Configuration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialMediaItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $posts = is_array($this->posts) ? array_values($this->posts) : [];
        $firstPost = $posts[0] ?? null;

        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'title' => $firstPost['title'] ?? $this->title,
            'subtitle' => $this->subtitle,
            'description' => $firstPost['description'] ?? $this->description,
            'url' => $this->url ?: ($firstPost['url'] ?? null),
            'profile_url' => $this->url,
            'embed_url' => $firstPost['embed_url'] ?? $this->embed_url,
            'thumbnail_url' => $firstPost['thumbnail_url'] ?? $this->thumbnail_url,
            'author_name' => $firstPost['author_name'] ?? $this->author_name,
            'is_embeddable' => filled($this->embed_url) || ! empty($posts),
            'posts_count' => count($posts),
            'posts' => $posts,
            'show_in_community' => (bool) $this->show_in_community,
            'show_in_footer' => (bool) $this->show_in_footer,
            'sort_order' => (int) $this->sort_order,
        ];
    }
}
