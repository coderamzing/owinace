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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->longText('description')->nullable();
            $table->decimal('expected_value', 10, 2);
            $table->decimal('actual_value', 10, 2);
            $table->decimal('cost', 10, 2);
            $table->timestamp('next_follow_up')->nullable();
            $table->timestamp('conversion_date')->nullable();
            $table->longText('notes')->nullable();
            $table->unsignedBigInteger('assigned_member_id')->nullable();
            $table->unsignedBigInteger('team_id');
            $table->foreignId('kanban_id')->constrained('lead_kanban')->onDelete('restrict');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('conversion_by_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->string('url', 500)->nullable();
            $table->timestamps();

            // Note: Add foreign key constraint for team_id when teams table is created
            // $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('source_id')->references('id')->on('lead_sources')->onDelete('set null');
            $table->foreign('assigned_member_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('conversion_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
            $table->index('team_id');
            $table->index('kanban_id');
            $table->index('source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
