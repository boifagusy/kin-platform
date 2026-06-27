<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('emergency_escalations', function (Blueprint $table) {
            // Add escalation level (orange, red, black)
            if (!Schema::hasColumn('emergency_escalations', 'level')) {
                $table->enum('level', ['orange', 'red', 'black'])->nullable()->after('priority');
            }

            // Add confidence score at time of escalation
            if (!Schema::hasColumn('emergency_escalations', 'confidence_score')) {
                $table->integer('confidence_score')->nullable()->after('level');
            }

            // Add escalation timestamps
            if (!Schema::hasColumn('emergency_escalations', 'escalated_at')) {
                $table->timestamp('escalated_at')->nullable()->after('confidence_score');
            }

            if (!Schema::hasColumn('emergency_escalations', 'timeout_at')) {
                $table->timestamp('timeout_at')->nullable()->after('escalated_at');
            }

            // Add retry count
            if (!Schema::hasColumn('emergency_escalations', 'retry_count')) {
                $table->integer('retry_count')->default(0)->after('timeout_at');
            }

            // Add escalation reason
            if (!Schema::hasColumn('emergency_escalations', 'reason')) {
                $table->text('reason')->nullable()->after('retry_count');
            }

            // Add safety_incident_id relationship
            if (!Schema::hasColumn('emergency_escalations', 'safety_incident_id')) {
                $table->foreignId('safety_incident_id')->nullable()->constrained()->onDelete('set null')->after('user_id');
            }

            // Add indexes
            $table->index('level');
            $table->index('status');
            $table->index('escalated_at');
        });
    }

    public function down()
    {
        Schema::table('emergency_escalations', function (Blueprint $table) {
            $table->dropForeign(['safety_incident_id']);
            $table->dropColumn([
                'level',
                'confidence_score',
                'escalated_at',
                'timeout_at',
                'retry_count',
                'reason',
                'safety_incident_id'
            ]);
        });
    }
};
