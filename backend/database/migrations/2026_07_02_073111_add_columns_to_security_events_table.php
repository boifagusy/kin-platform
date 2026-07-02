<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('security_events', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('security_events', 'event_type')) {
                $table->string('event_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('security_events', 'severity')) {
                $table->string('severity')->default('info')->after('event_type');
            }
            if (!Schema::hasColumn('security_events', 'source_ip')) {
                $table->string('source_ip')->nullable()->after('severity');
            }
            if (!Schema::hasColumn('security_events', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('source_ip');
            }
            if (!Schema::hasColumn('security_events', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('security_events', 'details')) {
                $table->json('details')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('security_events', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('details');
            }
            if (!Schema::hasColumn('security_events', 'resolved_by')) {
                $table->foreignId('resolved_by')->nullable()->after('resolved_at');
            }
        });
    }

    public function down()
    {
        Schema::table('security_events', function (Blueprint $table) {
            $table->dropColumn([
                'event_type',
                'severity',
                'source_ip',
                'user_agent',
                'user_id',
                'details',
                'resolved_at',
                'resolved_by',
            ]);
        });
    }
};
