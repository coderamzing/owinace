<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->decimal('rule_client_avghire', 5, 2)->nullable()->after('rule_client_avg_spent');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->dropColumn('rule_client_avghire');
        });
    }
};
