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
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Forms\Components\Actions\Action;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedUser;

    protected static string|UnitEnum|null $navigationGroup = 'Personal';

    protected static ?string $navigationLabel = 'Profile';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $title = 'My Profile';

    protected static ?string $slug = 'profile';

    protected string $view = 'filament.pages.profile';

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
        $this->mfaForm->fill($this->mfaData);
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
                    ->helperText('Upload a profile picture (max 2MB). Square images work best.')
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText(function () {
                        $user = Auth::user();
                        if ($user->pending_email) {
                            return 'A verification email has been sent to ' . $user->pending_email . '. Please check your inbox and verify the new email address.';
                        }
                        if (!$user->hasVerifiedEmail()) {
                            return 'Your email address is unverified. A verification link will be sent when you update your email.';
                        }
                        return 'If you change your email address, a verification email will be sent to the new address. Your email will only be updated after you verify it.';
                    }),
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
                    ->rules(['current_password'])
                    ->autocomplete('current-password'),
                TextInput::make('password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->rules([Password::defaults()])
                    ->autocomplete('new-password'),
                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->required()
                    ->same('password')
                    ->autocomplete('new-password'),
            ])
            ->statePath('passwordData');
    }

    /**
     * Get the multi-factor authentication form schema.
     */
    public function mfaForm(Schema $schema): Schema
    {
        if (! Filament::hasMultiFactorAuthentication()) {
            return $schema->components([]);
        }

        $user = Filament::auth()->user();

        $components = collect(Filament::getMultiFactorAuthenticationProviders())
            ->sort(fn ($multiFactorAuthenticationProvider): int => $multiFactorAuthenticationProvider->isEnabled($user) ? 0 : 1)
            ->map(fn ($multiFactorAuthenticationProvider): Component => Group::make($multiFactorAuthenticationProvider->getManagementSchemaComponents())
                ->statePath($multiFactorAuthenticationProvider->getId()))
            ->all();

        return $schema
            ->components($components)
            ->statePath('mfaData');
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $data = $this->profileForm->getState();
        
        $emailChanged = $user->email !== $data['email'];
        
        // Update name immediately
        $user->name = $data['name'];
        
        if ($emailChanged) {
            // Store pending email instead of updating immediately
            $token = Str::random(64);
            $user->pending_email = $data['email'];
            $user->email_change_token = hash('sha256', $token);
            $user->email_change_token_expires_at = now()->addHours(24);
            
            // Send verification email to the new email address
            $user->notify(new EmailChangeVerificationNotification($token));
            
            Notification::make()
                ->info()
                ->title('Email Change Requested')
                ->body('A verification email has been sent to ' . $data['email'] . '. Please check your inbox and click the verification link to complete the email change. Your email will remain ' . $user->email . ' until verified.')
                ->persistent()
                ->send();
        }

        $user->save();

        // Update the form data (keep current email, not pending)
        $this->profileData = [
            'name' => $user->name,
            'email' => $user->email,
        ];
        
        $this->profileForm->fill($this->profileData);

        if (!$emailChanged) {
            Notification::make()
                ->success()
                ->title('Profile Updated')
                ->body('Your profile information has been successfully updated.')
                ->send();
        }
    }

    public function updatePassword(): void
    {
        $data = $this->passwordForm->getState();
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        // Reset the password form
        $this->passwordData = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->passwordForm->fill($this->passwordData);

        Notification::make()
            ->success()
            ->title('Password Updated')
            ->body('Your password has been successfully updated.')
            ->send();
    }

}
