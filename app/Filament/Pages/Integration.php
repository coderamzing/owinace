<?php

namespace App\Filament\Pages;

use App\Models\Workspace;
use App\Traits\HasPermission;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Integration extends Page
{
    use HasPermission;

    protected static ?string $permission = 'settings.manage';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $title = 'Integration';

    protected static ?string $slug = 'integration';

    protected string $view = 'filament.pages.integration';

    public function mount(): void
    {
        abort_unless(static::hasPermissionTo('settings.manage'), 403);
        abort_unless($this->getWorkspace() !== null, 404);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetToken')
                ->label('Reset token')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset API token?')
                ->modalDescription('Bots, browser extensions, and other integrations using the current token will stop working until you update them with the new token.')
                ->action(fn () => $this->resetWorkspaceToken()),
        ];
    }

    public function resetWorkspaceToken(): void
    {
        $workspace = $this->getWorkspace();
        if ($workspace === null) {
            return;
        }

        $workspace->update([
            'token' => Workspace::generateUniqueToken($workspace->id),
        ]);

        Notification::make()
            ->title('Token reset')
            ->success()
            ->body('A new API token has been generated.')
            ->send();
    }

    public function getWorkspace(): ?Workspace
    {
        $workspaceId = session('workspace_id') ?? auth()->user()?->workspace_id;

        return $workspaceId ? Workspace::query()->find($workspaceId) : null;
    }
}
