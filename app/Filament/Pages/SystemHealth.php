<?php

namespace App\Filament\Pages;

use App\Models\LeadCost;
use App\Models\LeadGoal;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\Portfolio;
use App\Models\Team;
use App\Models\Workspace;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use UnitEnum;

class SystemHealth extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'System Health';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hide from main navigation, only accessible from user menu
    }

    protected static ?string $title = 'System Health';

    protected static ?string $slug = 'system-health';

    protected string $view = 'filament.pages.system-health';

    public function getTeamId(): ?int
    {
        return Session::get('team_id');
    }

    public function getWorkspaceId(): ?int
    {
        return Session::get('workspace_id');
    }

    public function getTeam(): ?Team
    {
        $teamId = $this->getTeamId();
        return $teamId ? Team::find($teamId) : null;
    }

    public function getWorkspace(): ?Workspace
    {
        $workspaceId = $this->getWorkspaceId();
        return $workspaceId ? Workspace::find($workspaceId) : null;
    }

    public function getSystemHealthData(): array
    {
        $teamId = $this->getTeamId();
        $workspace = $this->getWorkspace();

        if (!$teamId) {
            return [
                'kanban' => ['count' => 0, 'required' => 1, 'status' => 'error'],
                'sources' => ['count' => 0, 'required' => 1, 'status' => 'error'],
                'goals' => ['count' => 0, 'required' => 1, 'status' => 'error'],
                'tags' => ['count' => 0, 'required' => 1, 'status' => 'error'],
                'costs' => ['count' => 0, 'required' => 1, 'status' => 'error'],
                'credits' => ['count' => 0, 'required' => 10, 'status' => 'error'],
                'portfolios' => ['count' => 0, 'required' => 1, 'status' => 'error'],
            ];
        }

        // Get counts for each requirement
        $kanbanCount = LeadKanban::where('team_id', $teamId)->count();
        $sourcesCount = LeadSource::where('team_id', $teamId)->count();
        $goalsCount = LeadGoal::where('team_id', $teamId)->count();
        $tagsCount = LeadTag::where('team_id', $teamId)->count();
        $costsCount = LeadCost::where('team_id', $teamId)->count();
        $portfoliosCount = Portfolio::where('team_id', $teamId)->count();

        // Get workspace credits (sum of all credits)
        $totalCredits = 0;
        if ($workspace) {
            $totalCredits = $workspace->totalCredits();
        }

        return [
            'kanban' => [
                'count' => $kanbanCount,
                'required' => 1,
                'status' => $kanbanCount >= 1 ? 'success' : 'error',
                'link' => url('/admin/lead-kanbans'),
                'label' => 'Kanban Stages'
            ],
            'sources' => [
                'count' => $sourcesCount,
                'required' => 2,
                'status' => $sourcesCount > 1 ? 'success' : 'error',
                'link' => url('/admin/lead-sources'),
                'label' => 'Lead Sources'
            ],
            'goals' => [
                'count' => $goalsCount,
                'required' => 2,
                'status' => $goalsCount > 1 ? 'success' : 'error',
                'link' => url('/admin/lead-goals'),
                'label' => 'Goals'
            ],
            'tags' => [
                'count' => $tagsCount,
                'required' => 2,
                'status' => $tagsCount > 1 ? 'success' : 'error',
                'link' => url('/admin/lead-tags'),
                'label' => 'Tags'
            ],
            'costs' => [
                'count' => $costsCount,
                'required' => 2,
                'status' => $costsCount > 1 ? 'success' : 'error',
                'link' => url('/admin/lead-costs'),
                'label' => 'Costs'
            ],
            'credits' => [
                'count' => $totalCredits,
                'required' => 10,
                'status' => $totalCredits >= 10 ? 'success' : 'error',
                'link' => url('/admin/workspace-credits'),
                'label' => 'Workspace Credits'
            ],
            'portfolios' => [
                'count' => $portfoliosCount,
                'required' => 2,
                'status' => $portfoliosCount > 1 ? 'success' : 'error',
                'link' => url('/admin/portfolios'),
                'label' => 'Portfolios'
            ],
        ];
    }

    public function getOverallHealthPercentage(): int
    {
        $data = $this->getSystemHealthData();
        $total = count($data);
        $completed = 0;

        foreach ($data as $item) {
            if ($item['status'] === 'success') {
                $completed++;
            }
        }

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }

    public function getHealthStatus(): string
    {
        $percentage = $this->getOverallHealthPercentage();

        if ($percentage >= 100) {
            return 'Excellent';
        } elseif ($percentage >= 70) {
            return 'Good';
        } elseif ($percentage >= 40) {
            return 'Needs Attention';
        } else {
            return 'Critical';
        }
    }

    public function getHealthStatusColor(): string
    {
        $percentage = $this->getOverallHealthPercentage();

        if ($percentage >= 100) {
            return 'success';
        } elseif ($percentage >= 70) {
            return 'info';
        } elseif ($percentage >= 40) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}

