<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->text('job_do')->nullable()->after('matching_critieria');
            $table->text('job_dont')->nullable()->after('job_do');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->dropColumn(['job_do', 'job_dont']);
        });
    }
};
