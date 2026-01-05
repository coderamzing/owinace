<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analyticssource', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->integer('total_cost')->default(0);
            $table->integer('total_lead')->default(0);
            $table->integer('total_won')->default(0);
            $table->integer('total_lost')->default(0);
            $table->decimal('total_value', 10, 2)->nullable();
            $table->integer('total_expected_value')->default(0);
            $table->integer('total_roi')->default(0);
            $table->decimal('avg_cost_per_lead', 10, 2)->nullable();
            $table->string('title', 255)->nullable();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->timestamps(6);

            $table->foreign('source_id')->references('id')->on('lead_sources')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
            $table->index('source_id');
            $table->index('team_id');
            $table->index(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyticssource');
    }
};
