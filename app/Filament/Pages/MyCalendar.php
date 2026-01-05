<?php

namespace App\Filament\Pages;

use App\Models\Lead;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use App\Traits\HasPermission;

class MyCalendar extends Page
{
    use HasPermission;
    
    protected static ?string $permission = 'calender.my';
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $navigationLabel = 'My Calendar';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'My Calendar';

    protected static ?string $slug = 'my-calendar';

    protected string $view = 'filament.pages.my-calendar';

    public function getUpcomingFollowUps()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return collect([]);
        }

        $teamId = session('team_id');
        
        if (!$teamId) {
            return collect([]);
        }

        return Lead::where('team_id', $teamId)
            ->where('assigned_member_id', $userId)
            ->whereNotNull('next_follow_up')
            ->where('next_follow_up', '>=', now())
            ->where('is_archived', false)
            ->with(['assignedMember', 'source', 'kanban'])
            ->orderBy('next_follow_up', 'asc')
            ->get()
            ->groupBy(function ($lead) {
                return $lead->next_follow_up->format('Y-m-d');
            });
    }

    public function getTodayFollowUps()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return collect([]);
        }

        $teamId = session('team_id');
        
        if (!$teamId) {
            return collect([]);
        }

        $today = now()->startOfDay();
        $tomorrow = now()->copy()->addDay()->startOfDay();

        return Lead::where('team_id', $teamId)
            ->where('assigned_member_id', $userId)
            ->whereNotNull('next_follow_up')
            ->whereBetween('next_follow_up', [$today, $tomorrow])
            ->where('is_archived', false)
            ->with(['assignedMember', 'source', 'kanban'])
            ->orderBy('next_follow_up', 'asc')
            ->get();
    }

    public function getThisWeekFollowUps()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return collect([]);
        }

        $teamId = session('team_id');
        
        if (!$teamId) {
            return collect([]);
        }

        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return Lead::where('team_id', $teamId)
            ->where('assigned_member_id', $userId)
            ->whereNotNull('next_follow_up')
            ->whereBetween('next_follow_up', [$startOfWeek, $endOfWeek])
            ->where('is_archived', false)
            ->with(['assignedMember', 'source', 'kanban'])
            ->orderBy('next_follow_up', 'asc')
            ->get();
    }
}

