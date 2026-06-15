<?php

namespace App\Filament\Resources\Portfolios\Pages;

use App\Filament\Resources\BaseListRecords;
use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Traits\HasPermission;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;

class ListPortfolios extends BaseListRecords
{
    use HasPermission;

    protected static string $resource = PortfolioResource::class;

    protected string $searchPlaceholder = 'Search portfolios by title, keywords...';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->size('lg')
                ->url(fn () => PortfolioResource::getUrl('import'))
                ->visible(fn () => self::hasPermissionTo('portfolio.import')),

            CreateAction::make()
                ->modalHeading('Create Portfolio')
                ->modalSubmitActionLabel('Create')
                ->color('primary')
                ->size('lg')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by_id'] = auth()->id();

                    // Set team_id from session if available
                    $teamId = session('team_id');
                    if ($teamId && ! isset($data['team_id'])) {
                        $data['team_id'] = $teamId;
                    }

                    return $data;
                }),
        ];
    }
}
