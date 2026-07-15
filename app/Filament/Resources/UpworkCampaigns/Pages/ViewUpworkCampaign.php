<?php

namespace App\Filament\Resources\UpworkCampaigns\Pages;

use App\Filament\Resources\UpworkCampaigns\Concerns\TestsUpworkCampaignJobs;
use App\Filament\Resources\UpworkCampaigns\UpworkCampaignResource;
use App\Models\UpworkCampaign;
use App\Services\CampaignMatchWebhookService;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUpworkCampaign extends ViewRecord
{
    use TestsUpworkCampaignJobs;

    protected static string $resource = UpworkCampaignResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->data = array_merge($this->data ?? [], [
            'test_job_description' => '',
            'test_job_questions' => [],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function testWebhook(): void
    {
        /** @var UpworkCampaign $record */
        $record = $this->getRecord();

        if (blank($record->webhook_url)) {
            Notification::make()
                ->title('No webhook URL')
                ->body('Add a match webhook URL on the edit page first.')
                ->warning()
                ->send();

            return;
        }

        $result = app(CampaignMatchWebhookService::class)->sendTest($record);

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
}
