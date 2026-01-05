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
        Schema::create('analyticsgoal', function (Blueprint $table) {
            $table->id();
            $table->string('fullname', 255)->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->string('goal_type', 20);
            $table->decimal('acheived', 10, 2)->nullable();
            $table->decimal('progress_value', 10, 2)->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps(6);

            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('team_id');
            $table->index('user_id');
            $table->index(['month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyticsgoal');
    }
};
