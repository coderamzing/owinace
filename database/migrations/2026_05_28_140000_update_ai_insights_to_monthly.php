<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_insights', function (Blueprint $table) {
            if (Schema::hasColumn('ai_insights', 'week_key')) {
                $table->dropUnique(['team_id', 'week_key']);
            }

            if (Schema::hasColumn('ai_insights', 'week')) {
                $table->dropIndex(['team_id', 'year', 'week']);
            }

            if (Schema::hasColumn('ai_insights', 'week')) {
                $table->dropColumn('week');
            }

            if (Schema::hasColumn('ai_insights', 'week_key')) {
                $table->dropColumn('week_key');
            }

            if (! Schema::hasColumn('ai_insights', 'month')) {
                $table->unsignedTinyInteger('month')->after('year');
            }

            $table->index(['team_id', 'year', 'month']);
            $table->unique(['team_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::table('ai_insights', function (Blueprint $table) {
            if (Schema::hasColumn('ai_insights', 'month')) {
                $table->dropUnique(['team_id', 'year', 'month']);
                $table->dropIndex(['team_id', 'year', 'month']);
                $table->dropColumn('month');
            }

            if (! Schema::hasColumn('ai_insights', 'week')) {
                $table->unsignedTinyInteger('week')->after('year');
            }

            if (! Schema::hasColumn('ai_insights', 'week_key')) {
                $table->string('week_key', 10)->after('week');
            }

            $table->index(['team_id', 'year', 'week']);
            $table->unique(['team_id', 'week_key']);
        });
    }
};

