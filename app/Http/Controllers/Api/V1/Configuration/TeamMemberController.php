<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Configuration\TeamMemberResource;
use App\Models\TeamMember;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    use ApiResponse;

    /**
     * Devuelve el Organigrama del Equipo de Servidores ordenado jerárquicamente (Niveles 1 a 5).
     */
    public function index(Request $request): JsonResponse
    {
        $query = TeamMember::query()
            ->with(['user.ledMinistries', 'user.servedMinistries'])
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('role_group')) {
            $query->where('role_group', (string) $request->query('role_group'));
        }

        $members = $query->get();
        $cleanLabels = TeamMember::roleGroupCleanLabels();

        $orgChart = [];
        foreach ($cleanLabels as $roleKey => $roleLabel) {
            $levelMembers = $members->where('role_group', $roleKey)->values();
            if ($levelMembers->isEmpty()) {
                continue;
            }

            $orgChart[] = [
                'level' => TeamMember::hierarchyLevel($roleKey),
                'role_group' => $roleKey,
                'label' => $roleLabel,
                'members' => TeamMemberResource::collection($levelMembers)->resolve(),
            ];
        }

        return $this->successResponse([
            'org_chart' => $orgChart,
            'items' => TeamMemberResource::collection($members)->resolve(),
        ]);
    }
}
