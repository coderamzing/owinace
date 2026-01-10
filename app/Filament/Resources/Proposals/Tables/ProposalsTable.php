<?php

namespace App\Filament\Resources\Proposals\Tables;

use App\Models\TeamMember;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Traits\HasPermission;

class ProposalsTable
{
    use HasPermission;
    public static function configure(Table $table): Table
    {
        return $table
            ->searchable(false)
            ->columns([
                TextColumn::make('title')
                    ->limit(60)
                    ->sortable(),
                TextColumn::make('keywords')
                    ->limit(40),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('search')
                    ->label('Full Text')
                    ->form([
                        TextInput::make('value')
                            ->placeholder('Search title, description, keywords')
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
                                ->orWhere('keywords', 'like', '%' . $value . '%');
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return filled($data['value'] ?? null)
                            ? 'Search: ' . $data['value']
                            : null;
                    }),
                SelectFilter::make('user_id')
                    ->label('')
                    ->placeholder('Member')
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
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->persistFiltersInSession()
            ->filtersFormColumns(2)
            ->filtersResetActionPosition(FiltersResetActionPosition::Footer)
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Proposal')
                    ->modalSubmitActionLabel('Save')
                    ->slideOver()
                    ->visible(fn ($record) => self::hasPermissionTo('proposal.edit')),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Proposal')
                    ->modalDescription('This will permanently remove the proposal.')
                    ->modalSubmitActionLabel('Delete')
                    ->visible(fn ($record) => self::hasPermissionTo('proposal.delete'))
                    ->color('danger'),
            ])
            ->bulkActions([]);
    }
}

