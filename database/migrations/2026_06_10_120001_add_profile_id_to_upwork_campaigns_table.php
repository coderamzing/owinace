<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->foreignId('profile_id')
                ->nullable()
                ->after('team_id')
                ->constrained('upwork_profiles')
                ->nullOnDelete();

            $table->index('profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_id');
        });
    }
};
