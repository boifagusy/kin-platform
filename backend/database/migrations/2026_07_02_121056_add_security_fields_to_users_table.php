<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('onboarding_completed');
            }
            if (!Schema::hasColumn('users', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('is_locked');
            }
            if (!Schema::hasColumn('users', 'locked_reason')) {
                $table->text('locked_reason')->nullable()->after('locked_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'locked_reason']);
        });
    }
};
