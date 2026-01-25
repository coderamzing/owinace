<?php

namespace App\Services;

use App\Models\AiInsight;
use App\Models\Lead;
use App\Models\LeadKanban;
use App\Models\LeadSource;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AIInsightService
{
    /**
     * Generate and store AI insights for a specific team and week.
     *
     * @param Team $team
     * @param Carbon $weekStart ISO week start date (e.g. Monday)
     * @return AiInsight|null
     */
    public function generateWeeklyInsightForTeam(Team $team, Carbon $weekStart): ?AiInsight
    {
        $weekStart = $weekStart->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $year = (int) $weekStart->year;
        $week = (int) $weekStart->isoWeek;
        $weekKey = sprintf('%d-W%02d', $year, $week);

        // Collect basic lead metrics for the week
        $leadsQuery = Lead::withoutTeam()
            ->where('team_id', $team->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd]);

        $leads = $leadsQuery->get();

        if ($leads->isEmpty()) {
            // No activity this week; avoid unnecessary API calls
            return AiInsight::updateOrCreate(
                [
                    'team_id' => $team->id,
                    'week_key' => $weekKey,
                ],
                [
                    'year' => $year,
                    'week' => $week,
                    'summary' => 'No lead activity recorded for this week.',
                    'highlights' => [],
                    'recommendations' => [],
                    'raw_payload' => [],
                ]
            );
        }

        $totalLeads = $leads->count();
        $totalValue = (float) ($leads->sum('actual_value') ?? 0);
        $totalExpected = (float) ($leads->sum('expected_value') ?? 0);
        $totalCost = (float) ($leads->sum('cost') ?? 0);

        // Get kanban IDs for WON/LOST/OPEN
        $wonKanban = LeadKanban::withoutTeam()
            ->where('team_id', $team->id)
            ->where('code', 'WON')
            ->first();

        $lostKanban = LeadKanban::withoutTeam()
            ->where('team_id', $team->id)
            ->where('code', 'LOST')
            ->first();

        $openKanban = LeadKanban::withoutTeam()
            ->where('team_id', $team->id)
            ->where('code', 'OPEN')
            ->first();

        $wonCount = $wonKanban
            ? $leads->where('kanban_id', $wonKanban->id)->count()
            : 0;

        $lostCount = $lostKanban
            ? $leads->where('kanban_id', $lostKanban->id)->count()
            : 0;

        $openCount = $openKanban
            ? $leads->where('kanban_id', $openKanban->id)->count()
            : 0;

        $conversionRate = $totalLeads > 0 ? ($wonCount / $totalLeads) * 100 : 0;
        $roi = ($totalCost > 0 && $totalValue > 0)
            ? (($totalValue - $totalCost) / $totalCost) * 100
            : 0;

        // Per-source stats for the week
        $sources = LeadSource::withoutTeam()
            ->where('team_id', $team->id)
            ->get();

        $sourceStats = $sources->map(function (LeadSource $source) use ($team, $weekStart, $weekEnd) {
            $query = Lead::withoutTeam()
                ->where('team_id', $team->id)
                ->where('source_id', $source->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd]);

            $count = $query->count();

            if ($count === 0) {
                return null;
            }

            return [
                'name' => $source->name,
                'total_leads' => $count,
                'total_value' => (float) ($query->sum('actual_value') ?? 0),
                'total_cost' => (float) ($query->sum('cost') ?? 0),
            ];
        })->filter()->values()->all();

        $periodLabel = sprintf(
            '%s to %s',
            $weekStart->format('M d, Y'),
            $weekEnd->format('M d, Y')
        );

        $payload = [
            'team_name' => $team->name,
            'period' => $periodLabel,
            'year' => $year,
            'week' => $week,
            'summary_metrics' => [
                'total_leads' => $totalLeads,
                'won_leads' => $wonCount,
                'lost_leads' => $lostCount,
                'open_leads' => $openCount,
                'total_value' => $totalValue,
                'total_expected_value' => $totalExpected,
                'total_cost' => $totalCost,
                'conversion_rate' => $conversionRate,
                'roi' => $roi,
            ],
            'sources' => $sourceStats,
        ];

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a B2B sales and marketing analytics expert. '
                            . 'Given structured weekly lead data, generate concise insights. '
                            . 'Reply ONLY in JSON with keys: summary (string), highlights (string[]), '
                            . 'recommendations (string[]). Be specific and actionable, no fluff.',
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Here is the weekly lead performance data (JSON): ' . json_encode($payload),
                    ],
                ],
                'temperature' => 0.4,
                'response_format' => [
                    'type' => 'json_object',
                ],
            ]);

            $content = $response['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                throw new \RuntimeException('Empty response from OpenAI for AI insights');
            }

            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON returned by OpenAI for AI insights');
            }

            $summary = isset($decoded['summary']) ? (string) $decoded['summary'] : '';
            $highlights = isset($decoded['highlights']) && is_array($decoded['highlights'])
                ? $decoded['highlights']
                : [];
            $recommendations = isset($decoded['recommendations']) && is_array($decoded['recommendations'])
                ? $decoded['recommendations']
                : [];
        } catch (\Throwable $e) {
            Log::error('AIInsightService error: ' . $e->getMessage(), [
                'team_id' => $team->id,
                'week_key' => $weekKey,
            ]);

            // Fallback: store metrics without AI text
            $summary = 'AI insights could not be generated for this week due to an error.';
            $highlights = [];
            $recommendations = [];
        }

        return AiInsight::updateOrCreate(
            [
                'team_id' => $team->id,
                'week_key' => $weekKey,
            ],
            [
                'year' => $year,
                'week' => $week,
                'summary' => $summary,
                'highlights' => $highlights,
                'recommendations' => $recommendations,
                'raw_payload' => $payload,
            ]
        );
    }
}

