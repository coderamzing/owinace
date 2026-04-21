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
        Schema::create('upwork_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->smallInteger('is_active')->default(1);
            $table->smallInteger('max_connect_per_bid')->default(0);
            $table->string('search_url');
            $table->string('timezone')->default('Asia/Kolkata')->nullable();
            $table->smallInteger('max_daily_bid')->default(0);
            $table->smallInteger('auto_bidding')->default(0);
            $table->text('portfolios')->nullable();
            $table->text('ai_prompt')->nullable();
            $table->text('questions_context')->nullable();
            $table->decimal('rule_client_avg_spent', 12, 2)->nullable();
            $table->decimal('rule_max_interviews', 12, 2)->nullable();
            $table->decimal('rule_job_posted_ago', 12, 2)->nullable();
            $table->decimal('rule_max_proposal', 12, 2)->nullable();
            $table->time('rule_clock_in')->nullable();
            $table->time('rule_clock_out')->nullable();
            $table->foreignId('member_id')->nullable()->constrained('team_members')->nullOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->foreignId('kanban_id')->nullable()->constrained('lead_kanban')->nullOnDelete();
            $table->timestamps();

            $table->index('team_id');
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upwork_campaigns');
    }
};
