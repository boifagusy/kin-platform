<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WHY:
     * Stores the user's login PIN securely as a hash.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('login_pin_hash', 255)
                  ->nullable()
                  ->after('email');

        });
    }

    /**
     * WHY:
     * Removes login PIN field during rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn('login_pin_hash');

        });
    }
};
