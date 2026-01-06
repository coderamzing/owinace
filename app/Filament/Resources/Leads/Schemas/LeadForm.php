<?php

namespace App\Filament\Resources\Leads\Schemas;

use App\Models\Contact;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(2000)
                    ->columnSpanFull(),
                
                Select::make('kanban_id')
                    ->label('Status')
                    ->options(function () {
                        $teamId = session('team_id');
                        if (!$teamId) {
                            return [];
                        }
                        return LeadKanban::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                
                Select::make('source_id')
                    ->label('Source')
                    ->options(function () {
                        $teamId = session('team_id');
                        if (!$teamId) {
                            return [];
                        }
                        return LeadSource::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name', 'id');
                    })
                    ->searchable(),

                Select::make('tags')
                    ->label('Tags')
                    ->relationship(
                        name: 'tags',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $teamId = session('team_id');
                            if ($teamId) {
                                $query->where('team_id', $teamId);
                            }
                        },
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                
                Select::make('assigned_member_id')
                    ->label('Assigned To')
                    ->options(function () {
                        $workspaceId = session('workspace_id');
                        if (!$workspaceId) {
                            return [];
                        }
                        return User::where('workspace_id', $workspaceId)
                            ->pluck('name', 'id');
                    })
                    ->searchable(),
                
                TextInput::make('expected_value')
                    ->label('Expected Value')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(999999.99),
                
                TextInput::make('actual_value')
                    ->label('Actual Value')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(999999.99),
                
                TextInput::make('cost')
                    ->label('Cost')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(999999.99),
                
                DateTimePicker::make('next_follow_up')
                    ->label('Next Follow Up')
                    ->timezone('UTC'),
                
                DateTimePicker::make('conversion_date')
                    ->label('Conversion Date')
                    ->timezone('UTC'),
                
                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->maxLength(500)
                    ->columnSpanFull(),
                
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->maxLength(2000)
                    ->columnSpanFull(),
                
                Toggle::make('is_archived')
                    ->label('Archived')
                    ->default(false),
                
                // Contact Section (Optional)
                Section::make('Contact Information (Optional)')
                    ->description('Link existing contacts or create a new one for this lead.')
                    ->schema([
                        Select::make('existing_contact_ids')
                            ->label('Link Existing Contacts')
                            ->options(function () {
                                $teamId = session('team_id');
                                if (!$teamId) {
                                    return [];
                                }
                                return Contact::where('team_id', $teamId)
                                    ->get()
                                    ->mapWithKeys(function ($contact) {
                                        $label = collect([
                                            $contact->first_name,
                                            $contact->last_name,
                                            $contact->email ? "({$contact->email})" : null,
                                            $contact->company ? "- {$contact->company}" : null,
                                        ])->filter()->join(' ');
                                        return [$contact->id => $label];
                                    });
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->helperText('Select one or more existing contacts to link to this lead, or create a new one below.')
                            ->reactive(),
                        
                        TextInput::make('contact_first_name')
                            ->label('First Name')
                            ->maxLength(255)
                            ->disabled(fn (callable $get) => !empty($get('existing_contact_ids')))
                            ->dehydrated(fn (callable $get) => empty($get('existing_contact_ids'))),
                        
                        TextInput::make('contact_last_name')
                            ->label('Last Name')
                            ->maxLength(255)
                            ->disabled(fn (callable $get) => !empty($get('existing_contact_ids')))
                            ->dehydrated(fn (callable $get) => empty($get('existing_contact_ids'))),
                        
                        TextInput::make('contact_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(254)
                            ->disabled(fn (callable $get) => !empty($get('existing_contact_ids')))
                            ->dehydrated(fn (callable $get) => empty($get('existing_contact_ids'))),
                        
                        TextInput::make('contact_phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20)
                            ->disabled(fn (callable $get) => !empty($get('existing_contact_ids')))
                            ->dehydrated(fn (callable $get) => empty($get('existing_contact_ids'))),
                        
                        TextInput::make('contact_company')
                            ->label('Company')
                            ->maxLength(255)
                            ->disabled(fn (callable $get) => !empty($get('existing_contact_ids')))
                            ->dehydrated(fn (callable $get) => empty($get('existing_contact_ids'))),
                        
                        TextInput::make('contact_job_title')
                            ->label('Job Title')
                            ->maxLength(255)
                            ->disabled(fn (callable $get) => !empty($get('existing_contact_ids')))
                            ->dehydrated(fn (callable $get) => empty($get('existing_contact_ids'))),
                        
                        TextInput::make('contact_website')
                            ->label('Website')
                            ->url()
                            ->maxLength(200)
                            ->columnSpanFull()
                            ->disabled(fn (callable $get) => !empty($get('existing_contact_ids')))
                            ->dehydrated(fn (callable $get) => empty($get('existing_contact_ids'))),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
