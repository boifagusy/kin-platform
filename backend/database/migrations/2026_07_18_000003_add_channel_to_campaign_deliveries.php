<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('campaign_deliveries', 'channel')) {
            Schema::table('campaign_deliveries', function (Blueprint $table) {
                $table->string('channel')->default('push')->after('status');
            });
        }
        \DB::table('campaign_deliveries')->whereNull('channel')->update(['channel' => 'push']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('campaign_deliveries', 'channel')) {
            Schema::table('campaign_deliveries', function (Blueprint $table) {
                $table->dropColumn('channel');
            });
        }
    }
};
