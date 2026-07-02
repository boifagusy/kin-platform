<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('phone', 20);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('accuracy', 5, 2)->nullable();
            $table->float('speed', 5, 2)->nullable();
            $table->float('heading', 5, 2)->nullable();
            $table->string('provider', 20)->default('gps');
            $table->integer('battery_level')->nullable();
            $table->boolean('is_background')->default(false);
            $table->timestamp('tracked_at');
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index(['user_id', 'tracked_at']);
            $table->index(['phone', 'tracked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_trackings');
    }
};
