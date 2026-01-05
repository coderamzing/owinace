<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Session;

class TeamScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $teamId = $this->getTeamId();
        
        if ($teamId !== null) {
            $builder->where($model->getTable() . '.team_id', $teamId);
        }
    }

    /**
     * Get the team ID from session.
     *
     * @return int|null
     */
    protected function getTeamId(): ?int
    {
        // Get team_id from session
        $teamId = Session::get('team_id');
        
        return $teamId;
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        // Add a method to query without team scope
        $builder->macro('withoutTeam', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        // Add a method to query with a specific team
        $builder->macro('forTeam', function (Builder $builder, ?int $teamId) {
            return $builder->withoutGlobalScope($this)->where($builder->getModel()->getTable() . '.team_id', $teamId);
        });
    }
}

