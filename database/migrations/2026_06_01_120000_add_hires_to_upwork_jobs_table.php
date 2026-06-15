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
        Schema::table('upwork_jobs', function (Blueprint $table) {
            $table->unsignedInteger('hires')->default(0)->after('interviews');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upwork_jobs', function (Blueprint $table) {
            $table->dropColumn('hires');
        });
    }
};
