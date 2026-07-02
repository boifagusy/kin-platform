<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_condition');
            $table->json('actions');
            $table->integer('max_attempts')->default(3);
            $table->integer('retry_delay_seconds')->default(60);
            $table->boolean('escalate_on_failure')->default(true);
            $table->string('escalation_level')->default('high');
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_policies');
    }
};
