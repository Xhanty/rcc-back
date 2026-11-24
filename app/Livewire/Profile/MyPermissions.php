<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

class MyPermissions extends MyProfileComponent
{
    protected string $view = 'livewire.profile.my-permissions';

    public static $sort = 17;

    public bool $isSuperAdmin = false;

    /** @var list<array{label: string, actions: list<string>}> */
    public array $assignedModules = [];

    public function mount(): void
    {
        /** @var User|null $user */
        $user = filament('filament-breezy')->auth()->user();

        if (! $user) {
            return;
        }

        $this->isSuperAdmin = (bool) $user->is_super_admin;

        $schema = User::modulePermissionSchema();
        $effective = $user->getEffectivePermissions();

        $result = [];

        foreach ($schema as $moduleKey => $config) {
            $userActions = $effective[$moduleKey] ?? [];
            if (empty($userActions)) {
                continue;
            }

            $actionLabels = collect($config['actions'])
                ->filter(fn (string $label, string $actionKey): bool => in_array($actionKey, $userActions, true))
                ->values()
                ->all();

            $result[] = [
                'label' => $config['label'],
                'actions' => $actionLabels,
            ];
        }

        $this->assignedModules = $result;
    }
}
