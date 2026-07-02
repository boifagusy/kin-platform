<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('location_lat')->nullable();
            $table->string('location_lng')->nullable();
            $table->integer('location_accuracy')->nullable();
            $table->integer('battery_level')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'location_lat', 'location_lng', 'location_accuracy', 'battery_level', 'resolved_at']);
        });
    }
};
