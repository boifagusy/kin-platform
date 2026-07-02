<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('duress_pin_hash', 255)
                  ->nullable()
                  ->after('email');

            $table->timestamp('last_checkin_at')
                  ->nullable()
                  ->after('duress_pin_hash');

            $table->json('last_location')
                  ->nullable()
                  ->after('last_checkin_at');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'duress_pin_hash',
                'last_checkin_at',
                'last_location'
            ]);

        });
    }
};
