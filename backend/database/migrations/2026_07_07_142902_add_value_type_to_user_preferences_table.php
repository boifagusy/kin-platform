<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            // Rename 'key' to 'preference_key' (optional)
            $table->renameColumn('key', 'preference_key');
            // Add value_type column
            $table->string('value_type', 20)->nullable()->after('preference_key');
        });
    }

    public function down()
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn('value_type');
            $table->renameColumn('preference_key', 'key');
        });
    }
};
