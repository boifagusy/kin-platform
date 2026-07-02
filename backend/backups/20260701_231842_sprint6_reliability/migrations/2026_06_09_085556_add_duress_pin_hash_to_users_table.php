<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'duress_pin_hash')) {
                $table->string('duress_pin_hash')->nullable()->after('login_pin_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'duress_pin_hash')) {
                $table->dropColumn('duress_pin_hash');
            }
        });
    }
};
