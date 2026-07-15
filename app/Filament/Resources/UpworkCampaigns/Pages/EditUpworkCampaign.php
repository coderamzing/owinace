<?php

namespace App\Filament\Resources\UpworkCampaigns\Pages;

use App\Filament\Resources\UpworkCampaigns\Concerns\TestsUpworkCampaignJobs;
use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use App\Models\UpworkCampaign;
use App\Services\CampaignMatchWebhookService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUpworkCampaign extends EditRecord
{
    use TestsUpworkCampaignJobs;

    protected static string $resource = UpworkCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function testWebhook(): void
    {
        /** @var UpworkCampaign $record */
        $record = $this->getRecord();
        $url = trim((string) ($this->data['webhook_url'] ?? $record->webhook_url));

        if ($url === '') {
            Notification::make()
                ->title('No webhook URL')
                ->body('Enter a match webhook URL first.')
                ->warning()
                ->send();

            return;
        }

        $campaign = $record->replicate();
        $campaign->id = $record->id;
        $campaign->title = $record->title;
        $campaign->webhook_url = $url;

        $result = app(CampaignMatchWebhookService::class)->sendTest($campaign);

        if (! $result['success']) {
            Notification::make()
                ->title('Webhook test failed')
                ->body($result['message'])
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Webhook test sent')
            ->body($result['message'])
            ->success()
            ->send();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        foreach ([
            'test_job_description' => '',
            'test_job_questions' => [],
        ] as $key => $default) {
            if (! array_key_exists($key, $data)) {
                $data[$key] = $default;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with((string) $key, 'test_')) {
                unset($data[$key]);
            }
        }

        return parent::mutateFormDataBeforeSave($data);
    }
}
