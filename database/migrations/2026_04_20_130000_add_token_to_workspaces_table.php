<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique()->after('slug');
        });

        foreach (DB::table('workspaces')->whereNull('token')->cursor() as $row) {
            do {
                $token = Str::password(20);
            } while (DB::table('workspaces')->where('token', $token)->exists());

            DB::table('workspaces')->where('id', $row->id)->update(['token' => $token]);
        }

        DB::statement('ALTER TABLE workspaces MODIFY token VARCHAR(64) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn('token');
        });
    }
};
