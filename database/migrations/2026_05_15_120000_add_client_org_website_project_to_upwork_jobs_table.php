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
            $table->string('client_org')->nullable()->after('client_name');
            $table->string('client_website')->nullable()->after('client_org');
            $table->string('client_project')->nullable()->after('client_website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upwork_jobs', function (Blueprint $table) {
            $table->dropColumn(['client_org', 'client_website', 'client_project']);
        });
    }
};
