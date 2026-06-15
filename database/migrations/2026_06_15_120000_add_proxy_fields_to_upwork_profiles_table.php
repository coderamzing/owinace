<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upwork_profiles', function (Blueprint $table) {
            $table->string('proxy_host')->nullable()->after('is_active');
            $table->unsignedSmallInteger('proxy_port')->nullable()->after('proxy_host');
            $table->string('proxy_username')->nullable()->after('proxy_port');
            $table->text('proxy_password')->nullable()->after('proxy_username');
            $table->string('proxy_protocol', 10)->default('http')->after('proxy_password');
            $table->timestamp('proxy_validated_at')->nullable()->after('proxy_protocol');
            $table->string('proxy_last_ip', 45)->nullable()->after('proxy_validated_at');
        });
    }

    public function down(): void
    {
        Schema::table('upwork_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'proxy_host',
                'proxy_port',
                'proxy_username',
                'proxy_password',
                'proxy_protocol',
                'proxy_validated_at',
                'proxy_last_ip',
            ]);
        });
    }
};
