<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recovery_action_id')->constrained('recovery_actions');
            $table->string('incident_id')->nullable(); // Can be external ID
            $table->string('subsystem')->nullable();
            $table->string('trigger')->nullable();
            $table->string('status'); // pending, running, success, failed, rolled_back
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->boolean('escalated')->default(false);
            $table->text('escalation_reason')->nullable();
            $table->json('verification_result')->nullable();
            $table->timestamps();
            
            $table->index('incident_id');
            $table->index('subsystem');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_attempts');
    }
};
