<?php

namespace App\Filament\Pages;

use App\Models\NotificationPreference;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class NotificationPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedBell;
    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Notification Preferences';
    
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    protected static ?string $title = 'Notification Preferences';
    protected static ?string $slug = 'notification-preferences';
    protected string $view = 'filament.pages.notification-preferences';

    public ?array $data = [];

    public function mount(): void
    {
        // Only allow admin users
        if (auth()->user()->type !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $this->form->fill($this->getPreferencesData());
    }

    public function form(Schema $schema): Schema
    {
        // Get notification types based on user type
        $userType = auth()->user()->type;
        $notificationTypes = NotificationPreference::getNotificationTypesForUser($userType);

        // Build email toggles list
        $emailToggles = [];
        foreach ($notificationTypes as $type => $config) {
            $emailToggles[] = Toggle::make("{$type}.email_enabled")
                ->label($config['label'])
                ->helperText('Module: ' . ucfirst($config['module']) . ' | Permission: ' . $config['permission']);
        }

        // Build in-app toggles list
        $inAppToggles = [];
        foreach ($notificationTypes as $type => $config) {
            $inAppToggles[] = Toggle::make("{$type}.in_app_enabled")
                ->label($config['label'])
                ->helperText('Module: ' . ucfirst($config['module']) . ' | Permission: ' . $config['permission']);
        }

        return $schema
            ->components([
                Tabs::make('Notification Preferences')
                    ->tabs([
                        Tab::make('Email')
                            ->icon('heroicon-o-envelope')
                            ->schema($emailToggles),
                        Tab::make('In-App')
                            ->icon('heroicon-o-bell')
                            ->schema($inAppToggles),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        // Get form data
        $data = $this->form->getState();

        // Get notification types based on user type
        $userType = auth()->user()->type;
        $notificationTypes = NotificationPreference::getNotificationTypesForUser($userType);

           $processedData = [];
            foreach ($data as $type => $subData) {
                foreach ($subData as $subType => $fields) {
                    $key = $type . '.' . $subType;

                    $processedData[$key] = [
                        'email_enabled' => $fields['email_enabled'] ?? 0,
                        'in_app_enabled' => $fields['in_app_enabled'] ?? 0,
                    ];
                }
            }

        $userId = auth()->id();
        $workspaceId = auth()->user()->workspace_id;

        // Save preferences for each notification type
        foreach ($notificationTypes as $notificationType => $config) {
            $preferences = $processedData[$notificationType] ?? [];

            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $userId,
                    'workspace_id' => $workspaceId,
                    'notification_type' => $notificationType,
                ],
                [
                    'required_permission' => $config['permission'],
                    'email_enabled' => $preferences['email_enabled'] ?? true,
                    'in_app_enabled' => $preferences['in_app_enabled'] ?? true,
                ]
            );
        }

        // Show success notification
        $savedCount = NotificationPreference::where('user_id', $userId)
            ->where('workspace_id', $workspaceId)
            ->count();

        FilamentNotification::make()
            ->title('Notification preferences saved successfully (' . $savedCount . ' preferences)')
            ->success()
            ->send();
    }

   

    protected function getPreferencesData(): array
    {
        $preferences = NotificationPreference::where('user_id', auth()->id())
            ->get()
            ->keyBy('notification_type');

        // Get notification types based on user type
        $userType = auth()->user()->type;
        $notificationTypes = NotificationPreference::getNotificationTypesForUser($userType);

        $data = [];
        foreach ($notificationTypes as $type => $config) {
            $pref = $preferences->get($type);

            $parts = explode('.', $type); // e.g. "leave.request"
            $ref = &$data;
            foreach ($parts as $part) {
                if (! isset($ref[$part])) {
                    $ref[$part] = [];
                }
                $ref = &$ref[$part];
            }

            $ref['email_enabled'] = $pref?->email_enabled ?? true;
            $ref['in_app_enabled'] = $pref?->in_app_enabled ?? true;
           


        }

        return $data;
    }
}

