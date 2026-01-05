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
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->longText('description')->nullable();
            $table->string('color', 7);
            $table->integer('sort_order');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('team_id');
            $table->timestamps();

            // Note: Add foreign key constraint for team_id when teams table is created
            // $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_sources');
    }
};
