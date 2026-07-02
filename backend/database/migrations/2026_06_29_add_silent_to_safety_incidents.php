<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('safety_incidents', 'silent')) {
                $table->boolean('silent')->default(false)->after('confidence_score');
            }
        });

        Schema::table('incident_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('incident_notifications', 'silent')) {
                $table->boolean('silent')->default(false)->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            $table->dropColumn('silent');
        });

        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->dropColumn('silent');
        });
    }
};
