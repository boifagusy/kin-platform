<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('watchtower_alert_rules')) {
            Schema::create('watchtower_alert_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('metric_type', 50);
                $table->string('condition', 10);
                $table->decimal('threshold', 10, 2);
                $table->integer('duration')->default(0);
                $table->string('severity', 20)->default('warning');
                $table->string('action', 50)->nullable();
                $table->boolean('enabled')->default(true);
                $table->json('notify_channels')->nullable();
                $table->json('notify_roles')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('watchtower_alert_rules');
    }
};
