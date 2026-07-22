<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('safety_incidents', 'responding_by_user_id')) {
                $table->foreignId('responding_by_user_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('safety_incidents', 'responding_at')) {
                $table->timestamp('responding_at')->nullable()->after('responding_by_user_id');
            }
            if (!Schema::hasColumn('safety_incidents', 'notification_status')) {
                $table->string('notification_status')->default('pending')->after('responding_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            $table->dropColumn(['responding_by_user_id', 'responding_at', 'notification_status']);
        });
    }
};
