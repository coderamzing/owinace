<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_profiles', function (Blueprint $table) {
            $table->unique('proxy_last_ip');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_profiles', function (Blueprint $table) {
            $table->dropUnique(['proxy_last_ip']);
        });
    }
};
