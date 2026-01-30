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
        Schema::table('lead_sources', function (Blueprint $table) {
            $table->unique(['name', 'team_id'], 'lead_sources_name_team_id_unique');
        });

        Schema::table('lead_kanban', function (Blueprint $table) {
            $table->unique(['code', 'team_id'], 'lead_kanban_code_team_id_unique');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->unique(['team_id', 'user_id'], 'team_members_team_id_user_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_sources', function (Blueprint $table) {
            $table->dropUnique('lead_sources_name_team_id_unique');
        });

        Schema::table('lead_kanban', function (Blueprint $table) {
            $table->dropUnique('lead_kanban_code_team_id_unique');
        });

        Schema::table('team_members', function (Blueprint $table) {
            // Drop the unique constraint
            // Note: The original non-unique index should still exist (wasn't dropped in up())
            $table->dropUnique('team_members_team_id_user_id_unique');
        });
    }
};
