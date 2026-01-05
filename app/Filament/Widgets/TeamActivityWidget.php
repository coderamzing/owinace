<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Proposal;
use App\Models\AnalyticsGoal;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TeamActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Team Activity';

    protected static ?int $sort = 10;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return $table->query(User::query()->whereRaw('1 = 0'));
        }

        // Get the data directly using raw query to avoid Filament's query processing
        $activities = \Illuminate\Support\Facades\DB::select("
            (SELECT 'lead' as type, title as title, created_at as activity_date, assigned_member_id, NULL as user_id, created_at as sort_date
             FROM leads WHERE team_id = ?)
            UNION
            (SELECT 'proposal' as type, CONCAT('Proposal: ', title) as title, created_at as activity_date, NULL as assigned_member_id, user_id, created_at as sort_date
             FROM proposals WHERE team_id = ?)
            UNION
            (SELECT 'goal' as type, CONCAT('Goal: ', COALESCE(fullname, goal_type)) as title, created_at as activity_date, NULL as assigned_member_id, user_id, created_at as sort_date
             FROM analyticsgoal WHERE team_id = ?)
            ORDER BY sort_date DESC LIMIT 15
        ", [$teamId, $teamId, $teamId]);

        return $table
            ->records(function () use ($activities) {
                return collect($activities);
            })
            ->columns([
                IconColumn::make('type')
                    ->label('')
                    ->icon(function (string $state): string {
                        return match ($state) {
                            'lead' => 'heroicon-o-user-plus',
                            'proposal' => 'heroicon-o-document-text',
                            'goal' => 'heroicon-o-flag',
                            default => 'heroicon-o-clock',
                        };
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'lead' => 'primary',
                            'proposal' => 'warning',
                            'goal' => 'success',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('type')
                    ->label('Activity')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'lead' => 'New Lead',
                            'proposal' => 'New Proposal',
                            'goal' => 'Goal Created',
                            default => ucfirst($state),
                        };
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'lead' => 'primary',
                            'proposal' => 'warning',
                            'goal' => 'success',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('title')
                    ->label('Details')
                    ->limit(50),

                TextColumn::make('user_name')
                    ->label('By')
                    ->state(function ($record) {
                        // Try to get user from either assigned_member_id or user_id
                        $userId = $record->assigned_member_id ?: $record->user_id;
                        if ($userId) {
                            $user = \App\Models\User::find($userId);
                            return $user ? $user->name : 'Unknown';
                        }
                        return 'Unknown';
                    }),

                TextColumn::make('activity_date')
                    ->label('When')
                    ->dateTime('M j, g:i A'),
            ])
            ->paginated(false);
    }
}
