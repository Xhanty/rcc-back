<?php

namespace App\Http\Resources\V1\Configuration;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cleanLabels = TeamMember::roleGroupCleanLabels();

        $occupation = $this->occupation;
        $joinedYear = $this->joined_year;
        $joinedSince = $this->joined_since;
        $testimony = $this->testimony;
        $favoriteVerse = $this->favorite_verse;

        $hasPresentation = filled($occupation) || filled($joinedSince) || filled($testimony) || filled($favoriteVerse);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'title' => $this->display_title,
            'display_title' => $this->display_title,
            'level' => TeamMember::hierarchyLevel($this->role_group),
            'role_group' => $this->role_group,
            'role_group_label' => $cleanLabels[$this->role_group] ?? $this->role_group,
            'photo_url' => $this->photo_url,
            'occupation' => $occupation,
            'joined_year' => $joinedYear,
            'joined_since' => $joinedSince,
            'testimony' => $testimony,
            'favorite_verse' => $favoriteVerse,
            'has_presentation' => $hasPresentation,
            'presentation' => [
                'has_story' => $hasPresentation,
                'occupation' => $occupation,
                'joined_year' => $joinedYear,
                'joined_since' => $joinedSince,
                'testimony' => $testimony,
                'favorite_verse' => $favoriteVerse,
            ],
            'ministries' => $this->user ? $this->user->ledMinistries->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'role' => 'leader',
            ])->concat($this->user->servedMinistries->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'role' => 'servant',
            ]))->unique('id')->values()->all() : [],
        ];
    }
}
