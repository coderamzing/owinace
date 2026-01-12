<?php

namespace App\Filament\Pages;

use App\Notifications\EmailChangeVerificationNotification;
use BackedEnum;
use UnitEnum;
use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Facades\Filament;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUser;
    protected static string|UnitEnum|null $navigationGroup = 'Personal';
    protected static ?string $navigationLabel = 'Profile';

    protected static ?string $title = 'My Profile';
    protected static ?string $slug = 'profile';

    protected string $view = 'filament.pages.profile';

    // ✅ ADD THIS (TAB STATE)
    public string $activeTab = 'profile';

    // ✅ OPTIONAL: keep tab after refresh
    protected function getQueryString(): array
    {
        return [
            'activeTab' => ['except' => 'profile'],
        ];
    }

    public ?array $profileData = [];
    public ?array $passwordData = [];
    public ?array $mfaData = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->profileData = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $this->passwordData = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->profileForm->fill($this->profileData);
        $this->passwordForm->fill($this->passwordData);

        if (Filament::hasMultiFactorAuthentication()) {
            $this->mfaForm->fill($this->mfaData);
        }
    }

    public function profileForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label('Avatar')
                    ->collection('avatar')
                    ->image()
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->maxSize(2048)
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('profileData')
            ->model(Auth::user());
    }

    public function passwordForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->required()
                    ->rules(['current_password']),

                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->rules([Password::defaults()]),

                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->required()
                    ->same('password'),
            ])
            ->statePath('passwordData');
    }

    public function mfaForm(Schema $schema): Schema
    {
        if (! Filament::hasMultiFactorAuthentication()) {
            return $schema->components([]);
        }

        $user = Filament::auth()->user();

        $components = collect(Filament::getMultiFactorAuthenticationProviders())
            ->sort(fn ($provider) => $provider->isEnabled($user) ? 0 : 1)
            ->map(fn ($provider) => Group::make($provider->getManagementSchemaComponents())
                ->statePath($provider->getId()))
            ->all();

        return $schema
            ->components($components)
            ->statePath('mfaData');
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $data = $this->profileForm->getState();

        $user->update([
            'name' => $data['name'],
        ]);

        Notification::make()
            ->success()
            ->title('Profile Updated')
            ->send();
    }

    public function updatePassword(): void
    {
        $data = $this->passwordForm->getState();

        Auth::user()->update([
            'password' => Hash::make($data['password']),
        ]);

        $this->passwordForm->fill([
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        Notification::make()
            ->success()
            ->title('Password Updated')
            ->send();
    }
}
