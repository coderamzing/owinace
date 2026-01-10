<?php

namespace App\Filament\Resources\Teams\Tables;

use App\Models\Scopes\TeamScope;
use App\Models\TeamMember;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Team Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workspace.name')
                    ->label('Workspace')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->searchable(),
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('all_members_count')
                    ->label('Members')
                    ->counts('allMembers')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('members')
                    ->label('Members')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->slideOver()
                    ->modalHeading(fn ($record) => 'Team Members - ' . $record->name)
                    ->modalContent(function ($record) {
                        // Use withoutGlobalScope to bypass TeamScope that filters by session team_id
                        $members = TeamMember::withoutGlobalScope(TeamScope::class)
                            ->where('team_id', $record->id)
                            ->with('user')
                            ->orderBy('role', 'desc')
                            ->orderBy('status', 'desc')
                            ->orderBy('created_at', 'asc')
                            ->get();
                        
                        return view('filament.resources.teams.partials.members-slide-panel', [
                            'record' => $record,
                            'members' => $members,
                        ]);
                    })
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                EditAction::make()
                    ->modalHeading('Edit Team')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Preserve workspace_id - don't allow it to be changed
                        $data['workspace_id'] = $record->workspace_id;
                        
                        return $data;
                    }),
            ])
            ->bulkActions([]);
    }
}
