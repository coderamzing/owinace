<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('url', 2000)->nullable()->after('title');
            $table->timestamp('pinged_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn(['url', 'pinged_at']);
        });
    }
};
