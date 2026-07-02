<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('security_alert_rules', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('security_alert_rules', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('security_alert_rules', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('security_alert_rules', 'rule_type')) {
                $table->string('rule_type')->nullable()->after('description');
            }
            if (!Schema::hasColumn('security_alert_rules', 'threshold')) {
                $table->integer('threshold')->default(5)->after('rule_type');
            }
            if (!Schema::hasColumn('security_alert_rules', 'time_window')) {
                $table->integer('time_window')->default(15)->after('threshold');
            }
            if (!Schema::hasColumn('security_alert_rules', 'severity')) {
                $table->string('severity')->default('warning')->after('time_window');
            }
            if (!Schema::hasColumn('security_alert_rules', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('severity');
            }
            if (!Schema::hasColumn('security_alert_rules', 'actions')) {
                $table->json('actions')->nullable()->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('security_alert_rules', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'description',
                'rule_type',
                'threshold',
                'time_window',
                'severity',
                'is_active',
                'actions',
            ]);
        });
    }
};
