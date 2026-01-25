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
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('week');
            $table->string('week_key', 10); // e.g. 2026-W03
            $table->text('summary')->nullable();
            $table->json('highlights')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'year', 'week']);
            $table->unique(['team_id', 'week_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};

