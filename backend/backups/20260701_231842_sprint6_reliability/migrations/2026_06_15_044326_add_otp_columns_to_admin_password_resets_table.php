<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admin_password_resets', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('admin_password_resets', 'otp')) {
                $table->string('otp')->nullable()->after('token');
            }
            if (!Schema::hasColumn('admin_password_resets', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('created_at');
            }
            if (!Schema::hasColumn('admin_password_resets', 'used')) {
                $table->boolean('used')->default(false)->after('expires_at');
            }
        });
    }

    public function down()
    {
        Schema::table('admin_password_resets', function (Blueprint $table) {
            $table->dropColumn(['otp', 'expires_at', 'used']);
        });
    }
};
