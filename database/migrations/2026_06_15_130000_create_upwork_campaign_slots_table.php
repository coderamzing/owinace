<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upwork_campaign_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('upwork_campaigns')
                ->cascadeOnDelete();
            $table->time('clock_in');
            $table->time('clock_out');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('campaign_id');
        });

        DB::table('upwork_campaigns')
            ->whereNotNull('rule_clock_in')
            ->whereNotNull('rule_clock_out')
            ->orderBy('id')
            ->each(function (object $campaign): void {
                DB::table('upwork_campaign_slots')->insert([
                    'campaign_id' => $campaign->id,
                    'clock_in' => $campaign->rule_clock_in,
                    'clock_out' => $campaign->rule_clock_out,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->dropColumn(['rule_clock_in', 'rule_clock_out']);
        });
    }

    public function down(): void
    {
        Schema::table('upwork_campaigns', function (Blueprint $table) {
            $table->time('rule_clock_in')->nullable();
            $table->time('rule_clock_out')->nullable();
        });

        $slots = DB::table('upwork_campaign_slots')
            ->orderBy('campaign_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('campaign_id');

        foreach ($slots as $campaignId => $campaignSlots) {
            $first = $campaignSlots->first();
            if ($first === null) {
                continue;
            }

            DB::table('upwork_campaigns')
                ->where('id', $campaignId)
                ->update([
                    'rule_clock_in' => $first->clock_in,
                    'rule_clock_out' => $first->clock_out,
                ]);
        }

        Schema::dropIfExists('upwork_campaign_slots');
    }
};
