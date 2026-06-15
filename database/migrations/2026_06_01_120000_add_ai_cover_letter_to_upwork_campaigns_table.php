<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->smallInteger('ai_cover_letter')->default(1)->after('ai_prompt');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->dropColumn('ai_cover_letter');
        });
    }
};
