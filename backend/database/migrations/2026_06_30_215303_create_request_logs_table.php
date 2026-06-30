<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('path', 255);
            $table->integer('status_code');
            $table->integer('response_time')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'status_code']);
            $table->index(['created_at', 'response_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
