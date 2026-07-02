<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('checkin_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->time('checkin_time')->default('21:00:00');
            $table->integer('grace_minutes')->default(15);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('checkin_settings'); }
};
