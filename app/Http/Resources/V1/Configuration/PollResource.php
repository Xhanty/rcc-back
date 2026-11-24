<?php

namespace App\Http\Resources\V1\Configuration;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $options = $this->whenLoaded('options', function () {
            $totalVotes = (int) $this->options->sum('votes_count');

            return $this->options->map(function ($option) use ($totalVotes) {
                $count = (int) ($option->votes_count ?? 0);

                return [
                    'id' => $option->id,
                    'label' => $option->label,
                    'votes_count' => $count,
                    'percentage' => $totalVotes > 0 ? round(($count / $totalVotes) * 100, 1) : 0,
                ];
            })->values()->all();
        });

        $totalVotes = $this->requires_registration
            ? (int) ($this->votes_count ?? ($this->relationLoaded('options') ? $this->options->sum('votes_count') : 0))
            : ($this->relationLoaded('options')
                ? (int) $this->options->sum('votes_count')
                : (int) ($this->votes_count ?? 0));

        $votedOptionId = $this->resource->getAttribute('user_voted_option_id');
        $hasParticipated = (bool) $this->resource->getAttribute('user_has_participated') || $votedOptionId !== null;

        return [
            'id' => $this->id,
            'tag' => $this->tag,
            'question' => $this->question,
            'closing_label' => $this->closing_label,
            'closes_at' => $this->closes_at?->toIso8601String(),
            'requires_registration' => (bool) $this->requires_registration,
            'total_votes' => $totalVotes,
            'has_voted' => $hasParticipated,
            'voted_option_id' => $votedOptionId !== null ? (int) $votedOptionId : null,
            'options' => $this->requires_registration ? [] : $options,
        ];
    }
}
