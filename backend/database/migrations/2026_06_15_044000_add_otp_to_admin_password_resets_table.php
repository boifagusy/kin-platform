<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('admin_password_resets', function (Blueprint $table) {
            $table->string('otp')->nullable()->after('token');
            $table->timestamp('expires_at')->nullable()->after('created_at');
            $table->boolean('used')->default(false)->after('expires_at');
        });
    }

    public function down()
    {
        Schema::table('admin_password_resets', function (Blueprint $table) {
            $table->dropColumn(['otp', 'expires_at', 'used']);
        });
    }
};
