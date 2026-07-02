<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safety_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('score')->default(100);
            $table->string('level')->default('safe');
            $table->json('factors')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'calculated_at']);
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_scores');
    }
};
