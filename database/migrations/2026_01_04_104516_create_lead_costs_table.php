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
        Schema::create('lead_costs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->decimal('monthly_cost', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('lead_sources')->onDelete('set null');
            $table->index('team_id');
            $table->index('member_id');
            $table->index('source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_costs');
    }
};
