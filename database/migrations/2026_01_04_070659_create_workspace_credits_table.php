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
        Schema::create('workspace_credits', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type', 10);
            $table->integer('credits');
            $table->string('transaction_id', 100)->nullable();
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('triggered_by_id')->nullable();
            $table->unsignedBigInteger('workspace_id')->nullable();
            $table->timestamps();

            $table->foreign('triggered_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->index('workspace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_credits');
    }
};
