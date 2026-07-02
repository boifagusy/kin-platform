<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_trusts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_fingerprint', 255);
            $table->integer('trust_score')->default(50);
            $table->boolean('root_detected')->default(false);
            $table->boolean('emulator_detected')->default(false);
            $table->boolean('sim_changed')->default(false);
            $table->boolean('app_reinstalled')->default(false);
            $table->json('reasons')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'device_fingerprint']);
            $table->index('trust_score');
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_trusts');
    }
};
