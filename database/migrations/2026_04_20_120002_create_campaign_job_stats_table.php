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
        Schema::create('upwork_campaign_job_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('upwork_campaigns')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('upwork_jobs')->cascadeOnDelete();
            $table->smallInteger('is_matched')->default(0);
            $table->smallInteger('is_applied')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'job_id']);
            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upwork_campaign_job_stats');
    }
};
