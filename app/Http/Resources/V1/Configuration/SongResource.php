<?php

namespace App\Http\Resources\V1\Configuration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'lyrics' => $this->lyrics,
            'youtube_url' => $this->youtube_url,
            'spotify_url' => $this->spotify_url,
            'instagram_url' => $this->instagram_url,
        ];
    }
}
