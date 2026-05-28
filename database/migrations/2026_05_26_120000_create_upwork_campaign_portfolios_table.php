<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upwork_campaigns_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('upwork_campaigns')
                ->cascadeOnDelete();
            $table->foreignId('portfolio_id')
                ->constrained('portfolios')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campaign_id', 'portfolio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upwork_campaigns_portfolios');
    }
};
