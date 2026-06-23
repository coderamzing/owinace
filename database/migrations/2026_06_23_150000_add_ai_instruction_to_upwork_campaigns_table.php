<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->text('ai_instruction')->nullable()->after('ai_cover_letter');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->dropColumn('ai_instruction');
        });
    }
};
