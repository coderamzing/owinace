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
        Schema::create('lead_goals', function (Blueprint $table) {
            $table->id();
            $table->string('goal_type', 20);
            $table->string('period', 10);
            $table->decimal('target_value', 10, 2);
            $table->decimal('current_value', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('team_id');
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->index('team_id');
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_goals');
    }
};
