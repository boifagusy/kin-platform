<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WHY:
     * Track whether a user completed
     * Kin safety onboarding.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->boolean(
                'onboarding_completed'
            )
            ->default(false)
            ->after('duress_pin_hash');

        });
    }

    /**
     * WHY:
     * Remove onboarding flag.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn(
                'onboarding_completed'
            );

        });
    }
};

