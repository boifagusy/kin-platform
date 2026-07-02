<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recovery_attempts', function (Blueprint $table) {
            // Make recovery_action_id nullable since policies have multiple actions
            $table->foreignId('recovery_action_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('recovery_attempts', function (Blueprint $table) {
            $table->foreignId('recovery_action_id')->nullable(false)->change();
        });
    }
};
