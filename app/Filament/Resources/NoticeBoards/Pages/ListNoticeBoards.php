<?php

namespace App\Filament\Resources\NoticeBoards\Pages;

use App\Filament\Resources\NoticeBoards\NoticeBoardResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ListNoticeBoards extends BaseListRecords
{
    protected static string $resource = NoticeBoardResource::class;

    // Custom search placeholder for notice boards
    protected string $searchPlaceholder = 'Search notices by title, description...';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Create Notice')
                ->modalSubmitActionLabel('Create')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['workspace_id'] = session('workspace_id');
                    
                    if (!$data['workspace_id']) {
                        // Fallback to user's workspace_id if session is not set
                        $data['workspace_id'] = Auth::user()?->workspace_id;
                    }
                    
                    // Set team_id from session if available (can be null for all teams)
                    $teamId = session('team_id');
                    if (!isset($data['team_id'])) {
                        $data['team_id'] = $teamId;
                    }
                    
                    return $data;
                }),
        ];
    }

    protected function getBaseNoticeQuery(): Builder
    {
        $workspaceId = session('workspace_id') ?? Auth::user()?->workspace_id;

        return parent::getTableQuery()
            ->when($workspaceId, fn ($query) => $query->where('workspace_id', $workspaceId));
    }

    protected function getTableQuery(): Builder
    {
        return $this->getBaseNoticeQuery();
    }
}

