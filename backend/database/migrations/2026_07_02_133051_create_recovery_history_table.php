<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recovery_attempt_id')->constrained('recovery_attempts');
            $table->string('event_type'); // started, action_executed, verified, escalated, completed
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
            
            $table->index('recovery_attempt_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_history');
    }
};
