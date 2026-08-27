<?php

namespace App\Http\Resources\V1\Configuration;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EventResource extends JsonResource
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
            'slug' => $this->slug,
            'name' => $this->title,
            'image' => $this->banner_image_path
                ? (str_starts_with($this->banner_image_path, 'http') ? $this->banner_image_path : asset(Storage::url($this->banner_image_path)))
                : asset('images/notfound.png'),
            'description' => $this->short_description,
            'content' => $this->content,
            'modality' => $this->modality,
            'start_date' => Carbon::parse($this->start_datetime)->format('d/m/Y'),
            'start_time' => Carbon::parse($this->start_datetime)->format('H:i A'),
            'end_date' => Carbon::parse($this->end_datetime)->format('d/m/Y'),
            'end_time' => Carbon::parse($this->end_datetime)->format('H:i A'),
            'event_type' => EventTypeResource::make($this->whenLoaded('eventType')),

            $this->mergeWhen($this->modality === 'in_person', [
                'venue_name' => $this->venue_name,
                'address' => $this->address,
            ]),

            $this->mergeWhen($this->modality === 'virtual', [
                'live_url' => $this->live_url,
            ]),
        ];
    }
}
