<?php

namespace App\Traits;

use App\Models\Scopes\TeamScope;

trait TeamTraits
{

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new TeamScope);
    }

}