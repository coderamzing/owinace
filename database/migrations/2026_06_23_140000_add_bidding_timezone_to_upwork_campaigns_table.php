<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->string('bidding_timezone')->default('UTC')->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->dropColumn('bidding_timezone');
        });
    }
};
