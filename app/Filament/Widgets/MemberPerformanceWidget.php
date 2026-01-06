<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsLead;
use App\Models\TeamMember;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class MemberPerformanceWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 12;
    
    protected static ?string $heading = 'Member Performance';

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        $teamId = Session::get('team_id');

        if (!$teamId) {
            return $table->query(User::query()->whereRaw('1 = 0'));
        }

        // Get period from session
        $selectedPeriod = Session::get('analytics_period') ?? Carbon::now()->format('Y-m');
        $month = (int) Carbon::parse($selectedPeriod . '-01')->month;
        $year = (int) Carbon::parse($selectedPeriod . '-01')->year;

        return $table
            ->query(
                AnalyticsLead::query()
                    ->where('team_id', $teamId)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->with('user')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Member')
                    ->sortable(),

                TextColumn::make('total_lead')
                    ->label('Total Leads')
                    ->sortable(),

                TextColumn::make('total_won')
                    ->label('Won')
                    ->sortable(),

                TextColumn::make('total_lost')
                    ->label('Lost')
                    ->sortable(),

                TextColumn::make('conversion_rate')
                    ->label('Conversion Rate')
                    ->state(function (AnalyticsLead $record) {
                        $totalLeads = $record->total_lead ?? 0;
                        if ($totalLeads == 0) return '0%';

                        $wonLeads = $record->total_won ?? 0;
                        return number_format(($wonLeads / $totalLeads) * 100, 1) . '%';
                    })
                    ->sortable(false), // Can't sort computed field

                TextColumn::make('total_expected_value')
                    ->label('Total Value')
                    ->money('USD')
                    ->sortable(),
            ])
            ->defaultSort('total_lead', 'desc')
            ->paginated(false);
    }
}
