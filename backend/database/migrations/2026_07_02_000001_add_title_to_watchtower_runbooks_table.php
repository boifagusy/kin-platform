<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('watchtower_runbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('watchtower_runbooks', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('watchtower_runbooks', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
