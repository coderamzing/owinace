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
        Schema::create('upwork_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('questions')->nullable();
            $table->string('uid')->unique();
            $table->text('skills')->nullable();
            $table->string('url');
            $table->string('location')->nullable();
            $table->unsignedInteger('proposals')->nullable();
            $table->string('client_name')->nullable();
            $table->decimal('client_rating', 5, 2)->nullable();
            $table->decimal('client_totalspent', 12, 2)->nullable();
            $table->decimal('client_jobposted', 12, 2)->nullable();
            $table->unsignedInteger('client_openjob')->nullable();
            $table->decimal('client_hirerate', 5, 2)->nullable();
            $table->decimal('client_avgspent', 12, 2)->nullable();
            $table->decimal('client_avghourlyrate', 12, 2)->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->dateTime('client_since')->nullable();
            $table->unsignedInteger('invites_sent')->nullable();
            $table->string('type')->nullable();
            $table->unsignedInteger('client_hires')->nullable();
            $table->unsignedInteger('interviews')->nullable();
            $table->unsignedInteger('connects')->nullable();
            $table->smallInteger('is_expired')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upwork_jobs');
    }
};
