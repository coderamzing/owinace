<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\TeamMember;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Traits\HasPermission;
use Illuminate\Database\Eloquent\Model;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class LeadsTable
{
    use HasPermission;
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('assignedMember.avatar_url')
                    ->label('')
                    ->circular()
                    ->size(32)
                    ->tooltip(fn ($record) => $record->assignedMember?->name ?? 'Unassigned'),
                TextColumn::make('title')
                    ->label('Title')
                    //->color('primary')
                    //->weight('bold')
                    ->sortable(),
                TextColumn::make('kanban.name')
                    ->label('Status')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->kanban?->color) {
                            return $state;
                        }
                        $color = $record->kanban->color;
                        return "<span class=\"inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium\" style=\"background-color: {$color}; color: #ffffff;\">{$state}</span>";
                    })
                    ->html()
                    ->sortable(),
                TextColumn::make('source.name')
                    ->label('Source')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->source?->color) {
                            return $state;
                        }
                        $color = $record->source->color;
                        return "<span class=\"inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium\" style=\"background-color: {$color}; color: #ffffff;\">{$state}</span>";
                    })
                    ->html()
                    ->sortable(),
                // TextColumn::make('assignedMember.name')
                //     ->label('Assigned To')
                //     ->sortable()
                //     ->default('Unassigned'),
                TextColumn::make('expected_value')
                    ->label('Expected Value')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('actual_value')
                    ->label('Actual Value')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('next_follow_up')
                    ->label('Next Follow Up')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                // IconColumn::make('is_archived')
                //     ->label('Archived')
                //     ->boolean()
                //     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdBy.name')
                    ->label('By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('On')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('search')
                    ->label('Full Text')
                    ->form([
                        TextInput::make('value')
                            ->placeholder('Search title, description, notes')
                            ->autofocus(),
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if (!$value) {
                            return $query;
                        }

                        return $query->where(function ($q) use ($value) {
                            $q->where('title', 'like', '%' . $value . '%')
                                ->orWhere('description', 'like', '%' . $value . '%')
                                ->orWhere('notes', 'like', '%' . $value . '%');
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return filled($data['value'] ?? null)
                            ? 'Search: ' . $data['value']
                            : null;
                    }),
                SelectFilter::make('assigned_member_id')
                    ->label('Assigned Member')
                    ->placeholder('Assigned Member')
                    ->options(function () {
                        $teamId = session('team_id');

                        if (!$teamId) {
                            return [];
                        }

                        return TeamMember::where('team_id', $teamId)
                            ->where(function ($query) {
                                $query->whereNull('status')
                                    ->orWhere('status', 'active');
                            })
                            ->with('user')
                            ->get()
                            ->filter(fn ($member) => $member->user !== null)
                            ->mapWithKeys(fn ($member) => [
                                $member->user_id => $member->user->name ?? $member->email,
                            ])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('source_id')
                    ->label('Select Source')
                    ->placeholder('Source')
                    ->options(function () {
                        $teamId = session('team_id');

                        if (!$teamId) {
                            return [];
                        }

                        return LeadSource::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
                DateRangeFilter::make('created_at')
                    ->label('Created Between'),
                SelectFilter::make('kanban_id')
                    ->label('Select Kanban')
                    ->placeholder('Kanban')
                    ->options(function () {
                        $teamId = session('team_id');

                        if (!$teamId) {
                            return [];
                        }

                        return LeadKanban::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->recordActions([
                ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->visible(fn ($record) => self::hasPermissionTo('leads.view')),
                    EditAction::make()
                        ->modalHeading('Edit Lead')
                        ->modalSubmitActionLabel('Save')
                        ->slideOver()
                        ->visible(fn ($record) => self::hasPermissionTo('leads.edit'))
                        ->fillForm(function (Lead $record): array {
                            $record->loadMissing(['tags', 'contacts']);

                            $formData = $record->toArray();
                            $formData['tags'] = $record->tags->pluck('id')->toArray();
                            $formData['existing_contact_ids'] = $record->contacts->pluck('id')->toArray();

                            return $formData;
                        })
                        ->mutateFormDataUsing(function (array $data): array {
                            $teamId = session('team_id');
                            if ($teamId) {
                                $data['team_id'] = $teamId;
                            }

                            $data['_contact_data'] = [
                                'existing_contact_ids' => $data['existing_contact_ids'] ?? [],
                                'first_name' => $data['contact_first_name'] ?? null,
                                'last_name' => $data['contact_last_name'] ?? null,
                                'email' => $data['contact_email'] ?? null,
                                'phone_number' => $data['contact_phone_number'] ?? null,
                                'company' => $data['contact_company'] ?? null,
                                'job_title' => $data['contact_job_title'] ?? null,
                                'website' => $data['contact_website'] ?? null,
                            ];

                            unset(
                                $data['existing_contact_ids'],
                                $data['contact_first_name'],
                                $data['contact_last_name'],
                                $data['contact_email'],
                                $data['contact_phone_number'],
                                $data['contact_company'],
                                $data['contact_job_title'],
                                $data['contact_website']
                            );

                            return $data;
                        })
                        ->after(function (Model $record, array $data) {
                            $contactData = $data['_contact_data'] ?? null;

                            if (! $contactData) {
                                return;
                            }

                            $contactIdsToSync = [];

                            if (! empty($contactData['existing_contact_ids']) && is_array($contactData['existing_contact_ids'])) {
                                $contactIdsToSync = $contactData['existing_contact_ids'];
                            }

                            if (
                                ! empty($contactData['first_name']) ||
                                ! empty($contactData['last_name']) ||
                                ! empty($contactData['email']) ||
                                ! empty($contactData['phone_number']) ||
                                ! empty($contactData['company'])
                            ) {
                                $contact = Contact::create([
                                    // @phpstan-ignore argument.type
                                    'first_name' => $contactData['first_name'],
                                    'last_name' => $contactData['last_name'],
                                    'email' => $contactData['email'],
                                    'phone_number' => $contactData['phone_number'],
                                    'company' => $contactData['company'],
                                    'job_title' => $contactData['job_title'],
                                    'website' => $contactData['website'],
                                    'team_id' => session('team_id'),
                                ]);

                                $contactIdsToSync[] = $contact->id;
                            }

                            if (! empty($contactIdsToSync)) {
                                $record->contacts()->sync($contactIdsToSync);
                            }
                        }),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete Lead')
                        ->modalDescription('This will permanently remove the lead.')
                        ->modalSubmitActionLabel('Delete')
                        ->visible(fn ($record) => self::hasPermissionTo('leads.delete'))
                        ->color('danger'),
                ]),
            ]);
    }
}
