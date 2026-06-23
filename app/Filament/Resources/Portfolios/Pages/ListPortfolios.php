<?php

namespace App\Filament\Resources\Portfolios\Pages;

use App\Filament\Resources\BaseListRecords;
use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Models\Portfolio;
use App\Services\PortfolioUrlPingService;
use App\Traits\HasPermission;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class ListPortfolios extends BaseListRecords
{
    use HasPermission;

    protected static string $resource = PortfolioResource::class;

    protected static bool $urlPingedOnSave = false;

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
                })
                ->before(function (array $data): void {
                    self::assertUrlOnSave($data);
                })
                ->after(function (Portfolio $record): void {
                    self::storeUrlPingTimestamp($record);
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function assertUrlOnSave(array $data, ?Portfolio $record = null): void
    {
        self::$urlPingedOnSave = false;

        $url = trim((string) ($data['url'] ?? ''));

        if ($record !== null && $url === trim((string) $record->url)) {
            return;
        }

        try {
            app(PortfolioUrlPingService::class)->assertReachable($url);
            self::$urlPingedOnSave = true;
        } catch (\Throwable $e) {
            Notification::make()
                ->title('URL check failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }

    public static function storeUrlPingTimestamp(Portfolio $record): void
    {
        if (! self::$urlPingedOnSave) {
            return;
        }

        $record->forceFill([
            'pinged_at' => now(),
        ])->saveQuietly();

        self::$urlPingedOnSave = false;
    }
}
