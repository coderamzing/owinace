<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\GoalsOverviewWidget;
use App\Filament\Widgets\LeadsOverviewWidget;
use App\Filament\Widgets\LeadsByStageWidget;
use App\Filament\Widgets\LeadsBySourceWidget;
use App\Filament\Widgets\MemberPerformanceWidget;
use App\Filament\Widgets\GoalPerformanceTrackingWidget;
use App\Filament\Widgets\RecentLeadsWidget;
use App\Filament\Widgets\RevenueOverviewWidget;
use App\Filament\Widgets\LeadConversionFunnelWidget;
use App\Filament\Widgets\TeamActivityWidget;
use App\Filament\Widgets\LeadQualityWidget;
use App\Filament\Widgets\MonthlyCostPerLeadWidget;
use App\Models\Team;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use App\Traits\HasPermission;

class AnalyticsDashboard extends Page
{
    use HasPermission;
    
    protected static ?string $permission = 'analytics.full';
    
    public ?string $selectedPeriod = null;

    protected static ?string $navigationLabel = 'Analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?int $navigationSort = 1;

    protected static ?string $title = '';

    protected static ?string $slug = 'dashboard';

    protected string $view = 'filament.pages.analytics-dashboard';

    public function mount(): void
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $this->selectedPeriod = $currentMonth;
        Session::put('analytics_period', $this->selectedPeriod);
    }

    public function getTeam(): ?Team
    {
        $teamId = Session::get('team_id');
        return $teamId ? Team::find($teamId) : null;
    }

    public function getPeriodOptions(): array
    {
        $team = $this->getTeam();
        if (!$team || !$team->created_at) {
            $startDate = Carbon::now()->startOfMonth();
        } else {
            $startDate = Carbon::parse($team->created_at)->startOfMonth();
        }

        $endDate = Carbon::now()->endOfMonth();
        $options = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $value = $current->format('Y-m');
            $label = $current->format('F Y');
            $options[$value] = $label;
            $current->addMonth();
        }

        return array_reverse($options, true);
    }

    public function getCurrentPeriodLabel(): string
    {
        if (!$this->selectedPeriod) {
            return Carbon::now()->format('F Y');
        }
        return Carbon::parse($this->selectedPeriod . '-01')->format('F Y');
    }

    public function goToPreviousMonth(): void
    {
        if (!$this->selectedPeriod) {
            $this->selectedPeriod = Carbon::now()->format('Y-m');
        }

        $current = Carbon::parse($this->selectedPeriod . '-01');
        $previous = $current->copy()->subMonth();

        // Check if previous month is within valid range
        $team = $this->getTeam();
        $minDate = $team && $team->created_at 
            ? Carbon::parse($team->created_at)->startOfMonth()
            : Carbon::now()->startOfMonth();

        if ($previous->gte($minDate)) {
            $this->selectedPeriod = $previous->format('Y-m');
            Session::put('analytics_period', $this->selectedPeriod);
            $this->dispatch('$refresh');
        }
    }

    public function goToNextMonth(): void
    {
        if (!$this->selectedPeriod) {
            $this->selectedPeriod = Carbon::now()->format('Y-m');
        }

        $current = Carbon::parse($this->selectedPeriod . '-01');
        $next = $current->copy()->addMonth();
        $maxDate = Carbon::now()->endOfMonth();

        if ($next->lte($maxDate)) {
            $this->selectedPeriod = $next->format('Y-m');
            Session::put('analytics_period', $this->selectedPeriod);
            $this->dispatch('$refresh');
        }
    }

    public function canGoToPreviousMonth(): bool
    {
        if (!$this->selectedPeriod) {
            return false;
        }

        $current = Carbon::parse($this->selectedPeriod . '-01');
        $previous = $current->copy()->subMonth();

        $team = $this->getTeam();
        $minDate = $team && $team->created_at 
            ? Carbon::parse($team->created_at)->startOfMonth()
            : Carbon::now()->startOfMonth();

        return $previous->gte($minDate);
    }

    public function canGoToNextMonth(): bool
    {
        if (!$this->selectedPeriod) {
            return false;
        }

        $current = Carbon::parse($this->selectedPeriod . '-01');
        $next = $current->copy()->addMonth();
        $maxDate = Carbon::now()->endOfMonth();

        return $next->lte($maxDate);
    }

    public function updatedSelectedPeriod(): void
    {
        Session::put('analytics_period', $this->selectedPeriod);
        $this->dispatch('$refresh');
    }

    public function getColumns(): int | string | array
    {
        return 12; // 12-column grid system
    }

    protected function getWidgets(): array
    {
        return [
            GoalsOverviewWidget::class,
            LeadsOverviewWidget::class,
            MonthlyCostPerLeadWidget::class,
            LeadsByStageWidget::class,
            LeadsBySourceWidget::class,
            MemberPerformanceWidget::class,
            GoalPerformanceTrackingWidget::class,
            RevenueOverviewWidget::class,
            LeadQualityWidget::class,
            LeadConversionFunnelWidget::class,
            RecentLeadsWidget::class,
            //TeamActivityWidget::class,
        ];
    }
}
