<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('safety_incidents', 'confidence_score')) {
                $table->integer('confidence_score')->nullable()->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            $table->dropColumn('confidence_score');
        });
    }
};
