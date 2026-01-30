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
        Schema::create('tier_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tier_id')->constrained('tiers')->onDelete('restrict');
            $table->unsignedBigInteger('workspace_id')->nullable();
            $table->string('email', 255);
            $table->string('transaction_id', 100)->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('status', 50)->default('complete');
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->index('workspace_id');
            $table->index('tier_id');
            $table->index('transaction_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tier_orders');
    }
};
