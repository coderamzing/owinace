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
        Schema::create('analyticslead', function (Blueprint $table) {
            $table->id();
            $table->string('fullname', 255)->nullable();
            $table->integer('month');
            $table->integer('year');
            $table->integer('total_lead')->default(0);
            $table->integer('total_won')->default(0);
            $table->integer('total_lost')->default(0);
            $table->decimal('total_value', 10, 2)->nullable();
            $table->integer('total_cost')->default(0);
            $table->integer('total_expected_value')->default(0);
            $table->integer('total_roi')->default(0);
            $table->decimal('avg_cost_per_lead', 10, 2)->nullable();
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
        Schema::dropIfExists('analyticslead');
    }
};
