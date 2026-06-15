<?php

namespace App\Filament\Resources\UpworkProfiles\Pages;

use App\Filament\Resources\BaseListRecords;
use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use App\Filament\Resources\UpworkProfiles\UpworkProfileResource;
use App\Models\UpworkProfile;
use App\Services\ProxyValidationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class ListUpworkProfiles extends BaseListRecords
{
    protected static string $resource = UpworkProfileResource::class;

    protected string $searchPlaceholder = 'Search profiles by title, email, or code...';

    /** @var string|null Egress IP validated during the current save flow. */
    protected static ?string $pendingValidatedProxyIp = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('campaigns')
                ->label('Campaigns')
                ->icon('heroicon-o-megaphone')
                ->color('gray')
                ->size('lg')
                ->url(fn () => UpworkCampaignResource::getUrl('index')),

            CreateAction::make()
                ->modalHeading('Create Profile')
                ->modalSubmitActionLabel('Create')
                ->color('primary')
                ->size('lg')
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {
                    $teamId = session('team_id');
                    if ($teamId && $teamId > 0) {
                        $data['team_id'] = $teamId;
                    }

                    return $data;
                })
                ->before(function (array $data): void {
                    self::assertProxyOnSave($data);
                })
                ->after(function (UpworkProfile $record): void {
                    self::storeProxyValidation($record);
                }),
        ];
    }

    public function testProxyConnection(): void
    {
        $index = array_key_last($this->mountedActions);
        $data = is_int($index) ? ($this->mountedActions[$index]['data'] ?? []) : [];

        $record = $this->getMountedAction()?->getRecord();
        $record = $record instanceof UpworkProfile ? $record : null;

        $service = app(ProxyValidationService::class);
        $result = $service->validateForSave($data, $record);

        if (! $result['success']) {
            Notification::make()
                ->title('Proxy check failed')
                ->body($result['message'])
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Proxy connected')
            ->body($result['message'])
            ->success()
            ->send();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function assertProxyOnSave(array $data, ?UpworkProfile $record = null): void
    {
        try {
            self::$pendingValidatedProxyIp = app(ProxyValidationService::class)
                ->assertValidOnSave($data, $record);
        } catch (\Throwable $e) {
            self::$pendingValidatedProxyIp = null;

            Notification::make()
                ->title('Proxy validation failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }

    public static function storeProxyValidation(UpworkProfile $record): void
    {
        if (filled(self::$pendingValidatedProxyIp)) {
            $record->forceFill([
                'proxy_validated_at' => now(),
                'proxy_last_ip' => self::$pendingValidatedProxyIp,
            ])->save();

            self::$pendingValidatedProxyIp = null;

            return;
        }

        $result = app(ProxyValidationService::class)->validateForSave(
            $record->only([
                'proxy_host',
                'proxy_port',
                'proxy_username',
                'proxy_password',
                'proxy_protocol',
            ]),
            $record,
        );

        if ($result['success']) {
            $record->forceFill([
                'proxy_validated_at' => now(),
                'proxy_last_ip' => $result['ip'] ?? null,
            ])->save();
        }
    }
}
