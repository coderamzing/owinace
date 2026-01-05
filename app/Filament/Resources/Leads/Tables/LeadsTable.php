<?php

namespace App\Filament\Resources\Leads\Tables;

use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\TeamMember;
use Filament\Forms\Components\TextInput;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->sortable(),
                TextColumn::make('kanban.name')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('source.name')
                    ->label('Source')
                    ->sortable(),
                TextColumn::make('assignedMember.name')
                    ->label('Assigned To')
                    ->sortable()
                    ->default('Unassigned'),
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
                IconColumn::make('is_archived')
                    ->label('Archived')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
                    ->label('Source')
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
                SelectFilter::make('kanban_id')
                    ->label('Kanban')
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
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                EditAction::make()
                    ->modalHeading('Edit Lead')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver(),
            ]);
    }
}
