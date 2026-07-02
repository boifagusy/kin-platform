<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('class');
            $table->text('description')->nullable();
            $table->boolean('is_safe')->default(true);
            $table->boolean('is_rollbackable')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_actions');
    }
};
