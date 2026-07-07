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
            $table->string('category', 50);           // 'safety', 'notifications', 'privacy', 'appearance', 'account'
            $table->string('preference_key', 50);     // NOT 'key' (reserved word)
            $table->string('value_type', 20);         // 'boolean', 'integer', 'string', 'json'
            $table->text('value')->nullable();        // The actual value
            $table->timestamps();
            
            $table->unique(['user_id', 'category', 'preference_key']);
            $table->index(['user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
