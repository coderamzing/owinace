<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('scale', 100)->default('')->change();
            $table->integer('sort_order')->default(0)->change();
        });

        DB::table('portfolios')
            ->whereNull('scale')
            ->update(['scale' => '']);

        DB::table('portfolios')
            ->whereNull('sort_order')
            ->update(['sort_order' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('scale', 100)->default(null)->change();
            $table->integer('sort_order')->default(null)->change();
        });
    }
};

