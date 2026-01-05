<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LeadCosts\LeadCostResource;
use App\Filament\Resources\LeadGoals\LeadGoalResource;
use App\Filament\Resources\LeadKanbans\LeadKanbanResource;
use App\Filament\Resources\LeadSources\LeadSourceResource;
use App\Filament\Resources\LeadTags\LeadTagResource;
use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Filament\Resources\Teams\TeamResource;
use App\Filament\Resources\WorkspaceCredits\WorkspaceCreditResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use App\Traits\HasPermission;

class Settings extends Page
{
    use HasPermission;

    protected static ?string $permission = 'settings.manage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    
    protected static ?string $navigationLabel = 'Settings';
    
    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.settings';

    public function getTitle(): string
    {
        return 'Settings';
    }

    public function getSettingsCards(): array
    {
        $allCards = [
            [
                'title' => 'Source',
                'description' => 'Manage your team\'s lead sources and tracking',
                'icon' => 'heroicon-o-link',
                'url' => LeadSourceResource::getUrl('index'),
                'color' => 'primary',
                'resource' => LeadSourceResource::class,
            ],
            [
                'title' => 'Tag',
                'description' => 'Manage your team\'s lead tags and categorization',
                'icon' => 'heroicon-o-tag',
                'url' => LeadTagResource::getUrl('index'),
                'color' => 'success',
                'resource' => LeadTagResource::class,
            ],
            [
                'title' => 'Kanban',
                'description' => 'Manage your team\'s lead kanban columns and workflow',
                'icon' => 'heroicon-o-squares-2x2',
                'url' => LeadKanbanResource::getUrl('index'),
                'color' => 'warning',
                'resource' => LeadKanbanResource::class,
            ],
            [
                'title' => 'Teams',
                'description' => 'Manage teams and their work schedules for your workspace',
                'icon' => 'heroicon-o-user-group',
                'url' => TeamResource::getUrl('index'),
                'color' => 'info',
                'resource' => TeamResource::class,
            ],
            [
                'title' => 'Members',
                'description' => 'Manage team members and their roles across your workspace',
                'icon' => 'heroicon-o-users',
                'url' => TeamMemberResource::getUrl('index'),
                'color' => 'danger',
                'resource' => TeamMemberResource::class,
            ],
            [
                'title' => 'Cost',
                'description' => 'Manage lead costs and monthly expenses for your team',
                'icon' => 'heroicon-o-currency-dollar',
                'url' => LeadCostResource::getUrl('index'),
                'color' => 'success',
                'resource' => LeadCostResource::class,
            ],
            [
                'title' => 'Goal',
                'description' => 'Manage lead goals and track progress for your team members',
                'icon' => 'heroicon-o-flag',
                'url' => LeadGoalResource::getUrl('index'),
                'color' => 'warning',
                'resource' => LeadGoalResource::class,
            ],
            [
                'title' => 'Credit History',
                'description' => 'View workspace credit transactions and balance history',
                'icon' => 'heroicon-o-credit-card',
                'url' => WorkspaceCreditResource::getUrl('index'),
                'color' => 'info',
                'resource' => WorkspaceCreditResource::class,
            ],
        ];

        // Remove internal resource keys and return cards
        return array_map(function ($card) {
            unset($card['resource']);
            return $card;
        }, $allCards);
    }
}

