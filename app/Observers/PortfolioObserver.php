<?php

namespace App\Observers;

use App\Models\Portfolio;
use App\Models\WorkspaceCredit;
use Illuminate\Support\Facades\Auth;

class PortfolioObserver
{
    /**
     * Handle the Portfolio "creating" event (before creation).
     */
    public function creating(Portfolio $portfolio): bool
    {
        return $this->checkCreditsAvailable($portfolio, 'create');
    }

    /**
     * Handle the Portfolio "updating" event (before update).
     */
    public function updating(Portfolio $portfolio): bool
    {
        return $this->checkCreditsAvailable($portfolio, 'update');
    }

    /**
     * Handle the Portfolio "created" event.
     */
    public function created(Portfolio $portfolio): void
    {
        $this->debitCreditsForPortfolio($portfolio);
    }

    /**
     * Check if workspace has sufficient credits before creating/updating portfolio
     */
    private function checkCreditsAvailable(Portfolio $portfolio, string $action = 'create'): bool
    {
        $team = \App\Models\Team::find($portfolio->team_id);
        $workspace = $team->workspace;
        $creditCost = $action === 'create' ? config('credit.credit_portfolio',  0.5 ) : 0;
        $totalCredits = $workspace->totalCredits();

        if ($totalCredits < $creditCost) {
            $message = sprintf(
                'Insufficient credits to %s portfolio. Available: %.2f%s. Please purchase more credits.',
                $action,
                $totalCredits,
                $action === 'create' ? ', Required: ' . $creditCost : ''
            );
            throw new \Exception($message);
        }

        return true;
    }

    /**
     * Debit credits when a portfolio is created
     */
    private function debitCreditsForPortfolio(Portfolio $portfolio): void
    {
        $team = $portfolio->team;
        $workspace = $team->workspace;
        $creditCost = config('credit.credit_portfolio',  0.5 );

        $note = sprintf(
            'Portfolio created: "%s" - ID: %d (Team: %s)',
            $portfolio->title,
            $portfolio->id,
            $team->name
        );

        WorkspaceCredit::create([
            'workspace_id' => $workspace->id,
            'credits' => -$creditCost,
            'transaction_type' => 'USE',
            'transaction_id' => 'portfolio_' . $portfolio->id,
            'note' => $note,
            'triggered_by_id' => Auth::id() ?? $portfolio->created_by_id,
        ]);
    }
}

