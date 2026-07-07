<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('category', 50);      // 'safety', 'notifications', 'privacy', 'appearance', 'account'
            $table->string('key', 50);           // 'monitoring', 'sound', 'theme', etc.
            $table->text('value')->nullable();   // JSON or scalar value
            $table->timestamps();
            
            $table->unique(['user_id', 'category', 'key']);
            $table->index(['user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
