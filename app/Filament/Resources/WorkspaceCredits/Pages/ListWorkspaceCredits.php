<?php

namespace App\Filament\Resources\WorkspaceCredits\Pages;

use App\Filament\Resources\WorkspaceCredits\WorkspaceCreditResource;
use App\Filament\Resources\BaseListRecords;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ListWorkspaceCredits extends BaseListRecords
{
    protected static string $resource = WorkspaceCreditResource::class;
    
    protected string $searchPlaceholder = 'Search credit history...';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('buy_credits')
                ->label('Buy Credits')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->url('/admin/buy-credits'),
        ];
    }

    public function getHeader(): ?View
    {
        $user = Auth::user();
        $totalCredits = $user && $user->workspace ? $user->workspace->totalCredits() : 0;
        
        return view('filament.resources.workspace-credits.header', [
            'totalCredits' => $totalCredits,
        ]);
    }

    public function getTitle(): string
    {
        return 'Credit History';
    }

    public function getHeading(): string
    {
        return 'Credit History';
    }
}

