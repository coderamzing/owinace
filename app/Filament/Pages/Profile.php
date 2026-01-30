<?php

namespace App\Filament\Pages;

use App\Notifications\EmailChangeVerificationNotification;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    /* -----------------------------------------------------------------
     | Navigation (HIDDEN)
     |-----------------------------------------------------------------*/
    protected static BackedEnum|string|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = null;
    protected static ?string $navigationLabel = null;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /* -----------------------------------------------------------------
     | Page Config
     |-----------------------------------------------------------------*/
    protected static ?string $title = 'My Profile';
    protected static ?string $slug = 'profile';
    protected string $view = 'filament.pages.profile';

    /* -----------------------------------------------------------------
     | Tabs State
     |-----------------------------------------------------------------*/
    public string $activeTab = 'profile';

    protected function getQueryString(): array
    {
        return [
            'activeTab' => ['except' => 'profile'],
        ];
    }

    /* -----------------------------------------------------------------
     | Sessions
     |-----------------------------------------------------------------*/
    public function getSessionsProperty(): array
    {
        $user = Auth::user();
        $currentSessionId = session()->getId();
        
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120))->timestamp)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $isCurrentSession = $session->id === $currentSessionId;
                
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => $session->last_activity,
                    'is_current_session' => $isCurrentSession,
                    'device' => $this->parseUserAgent($session->user_agent),
                ];
            })
            ->toArray();
        
        return $sessions;
    }

    protected function parseUserAgent(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Unknown Device';
        }

        // Simple user agent parsing
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            if (preg_match('/iPhone/i', $userAgent)) {
                return 'iPhone';
            }
            if (preg_match('/iPad/i', $userAgent)) {
                return 'iPad';
            }
            if (preg_match('/Android/i', $userAgent)) {
                return 'Android Device';
            }
            return 'Mobile Device';
        }

        if (preg_match('/Windows/i', $userAgent)) {
            return 'Windows';
        }
        if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            return 'Mac';
        }
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }

        return 'Desktop';
    }

    public function logoutSession(string $sessionId): void
    {
        $currentSessionId = session()->getId();
        
        // Don't allow logging out the current session
        if ($sessionId === $currentSessionId) {
            Notification::make()
                ->danger()
                ->title('Cannot logout current session')
                ->body('Please use the logout button in the menu to logout from this session.')
                ->send();
            return;
        }

        $user = Auth::user();
        
        // Verify the session belongs to the user
        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if ($session) {
            DB::table('sessions')->where('id', $sessionId)->delete();
            
            Notification::make()
                ->success()
                ->title('Session logged out')
                ->body('The session has been successfully logged out.')
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title('Session not found')
                ->body('The session could not be found or does not belong to you.')
                ->send();
        }
    }

    /* -----------------------------------------------------------------
     | Form State
     |-----------------------------------------------------------------*/
    public ?array $profileData = [];
    public ?array $passwordData = [];
    public ?array $mfaData = [];

    /* -----------------------------------------------------------------
     | Mount
     |-----------------------------------------------------------------*/
    public function mount(): void
    {
        $user = Auth::user();

        $this->profileData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
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

    /* -----------------------------------------------------------------
     | Profile Form
     |-----------------------------------------------------------------*/
    public function profileForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label('Avatar')
                    ->collection('avatar')
                    ->image()
                    ->avatar()
                    ->circleCropper()
                    ->maxSize(2048)
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('+1 (555) 000-0000'),
            ])
            ->statePath('profileData')
            ->model(Auth::user());
    }

    /* -----------------------------------------------------------------
     | Password Form
     |-----------------------------------------------------------------*/
    public function passwordForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->password()
                    ->required()
                    ->rules(['current_password']),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->rules([Password::defaults()]),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required()
                    ->same('password'),
            ])
            ->statePath('passwordData');
    }

    /* -----------------------------------------------------------------
     | MFA Form
     |-----------------------------------------------------------------*/
    public function mfaForm(Schema $schema): Schema
    {
        if (! Filament::hasMultiFactorAuthentication()) {
            return $schema->components([]);
        }

        $user = Filament::auth()->user();

        $components = collect(Filament::getMultiFactorAuthenticationProviders())
            ->sort(fn ($provider) => $provider->isEnabled($user) ? 0 : 1)
            ->map(fn ($provider): Component => Group::make(
                $provider->getManagementSchemaComponents()
            )->statePath($provider->getId()))
            ->all();

        return $schema
            ->components($components)
            ->statePath('mfaData');
    }

    /* -----------------------------------------------------------------
     | Actions
     |-----------------------------------------------------------------*/
    public function updateProfile(): void
    {
        $data = $this->profileForm->getState();

        Auth::user()->update([
            'name' => $data['name'],
            'phone_number' => $data['phone_number'] ?? null,
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
