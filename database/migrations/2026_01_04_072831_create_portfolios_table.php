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
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('scale', 100);
            $table->longText('keywords');
            $table->string('title', 255);
            $table->longText('description');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order');
            $table->foreignId('created_by_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('team_id');
            $table->timestamps();

            // Note: Add foreign key constraint for team_id when teams table is created
            // $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->index('team_id');
            $table->index('created_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
