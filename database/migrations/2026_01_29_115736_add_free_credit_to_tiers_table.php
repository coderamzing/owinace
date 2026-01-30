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
        Schema::table('tiers', function (Blueprint $table) {
            $table->integer('free_credit')->default(100)->after('max_storage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->dropColumn('free_credit');
        });
    }
};
