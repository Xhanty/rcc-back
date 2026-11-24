<?php

namespace App\Http\Resources\V1\Configuration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MinistryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $leaders = $this->relationLoaded('leaders')
            ? $this->leaders->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])->values()->all()
            : [];

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'detail' => $this->detail,
            'image' => $this->resolveImageUrl($this->image_path),
            'leader' => $leaders[0] ?? null,
            'leaders' => $leaders,
            'servants' => $this->whenLoaded('servants', fn () => $this->servants->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])->values()->all()),
            'meeting_day' => $this->meeting_day,
            'meeting_time' => (filled($this->meeting_time) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $this->meeting_time))
                ? \Carbon\Carbon::createFromFormat('H:i', substr($this->meeting_time, 0, 5))->format('g:i A')
                : $this->meeting_time,
            'meeting_location' => $this->meeting_location,
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
