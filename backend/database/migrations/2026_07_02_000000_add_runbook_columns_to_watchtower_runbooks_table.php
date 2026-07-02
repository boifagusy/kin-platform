<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('watchtower_runbooks', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('watchtower_runbooks', 'trigger_condition')) {
                $table->string('trigger_condition')->nullable()->after('id');
            }
            if (!Schema::hasColumn('watchtower_runbooks', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('watchtower_runbooks', 'impact')) {
                $table->text('impact')->nullable()->after('description');
            }
            if (!Schema::hasColumn('watchtower_runbooks', 'recommended_actions')) {
                $table->text('recommended_actions')->nullable()->after('impact');
            }
            if (!Schema::hasColumn('watchtower_runbooks', 'estimated_recovery_time')) {
                $table->string('estimated_recovery_time')->nullable()->after('recommended_actions');
            }
            if (!Schema::hasColumn('watchtower_runbooks', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('estimated_recovery_time');
            }
        });
    }

    public function down()
    {
        Schema::table('watchtower_runbooks', function (Blueprint $table) {
            $table->dropColumn([
                'trigger_condition',
                'description',
                'impact',
                'recommended_actions',
                'estimated_recovery_time',
                'is_active',
            ]);
        });
    }
};
