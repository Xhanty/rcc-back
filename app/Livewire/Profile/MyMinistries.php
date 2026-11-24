<?php

namespace App\Livewire\Profile;

use App\Models\Ministry;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class MyMinistries extends MyProfileComponent
{
    protected string $view = 'livewire.profile.my-ministries';

    public static $sort = 14;

    public array $ministries = [];

    public ?array $communityRole = null;

    public function mount(): void
    {
        $user = filament('filament-breezy')->auth()->user();

        if (! $user) {
            return;
        }

        $teamMember = $user->teamMember;
        if ($teamMember) {
            $cleanLabels = TeamMember::roleGroupCleanLabels();
            $this->communityRole = [
                'role_group' => $teamMember->role_group,
                'role_group_label' => $cleanLabels[$teamMember->role_group] ?? $teamMember->role_group,
                'display_title' => $teamMember->display_title,
            ];
        }

        $led = $user->ledMinistries()
            ->orderBy('sort_order')
            ->get();

        $served = $user->servedMinistries()
            ->orderBy('sort_order')
            ->get();

        $combined = new Collection();

        foreach ($led as $ministry) {
            $combined->put($ministry->id, [
                'id' => $ministry->id,
                'title' => $ministry->title,
                'role' => 'Líder',
                'role_type' => 'leader',
                'schedule' => $this->formatSchedule($ministry),
                'location' => $ministry->meeting_location,
                'is_active' => (bool) $ministry->is_active,
            ]);
        }

        foreach ($served as $ministry) {
            if ($combined->has($ministry->id)) {
                continue;
            }

            $combined->put($ministry->id, [
                'id' => $ministry->id,
                'title' => $ministry->title,
                'role' => 'Servidor',
                'role_type' => 'servant',
                'schedule' => $this->formatSchedule($ministry),
                'location' => $ministry->meeting_location,
                'is_active' => (bool) $ministry->is_active,
            ]);
        }

        $this->ministries = $combined->values()->all();
    }

    protected function formatSchedule(Ministry $ministry): ?string
    {
        $time = $ministry->meeting_time;
        if (filled($time) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
            $time = Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
        }

        $parts = array_filter([$ministry->meeting_day, $time]);

        return count($parts) > 0 ? implode(' · ', $parts) : null;
    }
}
