<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('push_campaigns', 'channels')) {
            Schema::table('push_campaigns', function (Blueprint $table) {
                $table->json('channels')->nullable()->after('body');
            });
        }
        \DB::table('push_campaigns')->whereNull('channels')->update(['channels' => json_encode(['push' => true])]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('push_campaigns', 'channels')) {
            Schema::table('push_campaigns', function (Blueprint $table) {
                $table->dropColumn('channels');
            });
        }
    }
};
