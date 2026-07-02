<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('security_events', function (Blueprint $table) {
            // Add indexes for common queries
            if (!Schema::hasIndex('security_events', 'security_events_event_type_index')) {
                $table->index('event_type');
            }
            if (!Schema::hasIndex('security_events', 'security_events_severity_index')) {
                $table->index('severity');
            }
            if (!Schema::hasIndex('security_events', 'security_events_user_id_index')) {
                $table->index('user_id');
            }
            if (!Schema::hasIndex('security_events', 'security_events_source_ip_index')) {
                $table->index('source_ip');
            }
            if (!Schema::hasIndex('security_events', 'security_events_created_at_index')) {
                $table->index('created_at');
            }
            // Composite index for common combinations
            if (!Schema::hasIndex('security_events', 'security_events_event_type_created_at_index')) {
                $table->index(['event_type', 'created_at']);
            }
            if (!Schema::hasIndex('security_events', 'security_events_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
        });
    }

    public function down()
    {
        Schema::table('security_events', function (Blueprint $table) {
            $table->dropIndex('security_events_event_type_index');
            $table->dropIndex('security_events_severity_index');
            $table->dropIndex('security_events_user_id_index');
            $table->dropIndex('security_events_source_ip_index');
            $table->dropIndex('security_events_created_at_index');
            $table->dropIndex('security_events_event_type_created_at_index');
            $table->dropIndex('security_events_user_id_created_at_index');
        });
    }
};
