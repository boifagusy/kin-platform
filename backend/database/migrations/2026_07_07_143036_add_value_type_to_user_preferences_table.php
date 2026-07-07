<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->string('value_type', 20)->nullable()->after('key');
        });
    }

    public function down()
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn('value_type');
        });
    }
};
