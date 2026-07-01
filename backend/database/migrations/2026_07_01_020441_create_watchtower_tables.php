<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Watchtower Metrics
        if (!Schema::hasTable('watchtower_metrics')) {
            Schema::create('watchtower_metrics', function (Blueprint $table) {
                $table->id();
                $table->string('metric_type', 50);
                $table->string('metric_key', 100)->nullable();
                $table->json('metric_value');
                $table->timestamp('collected_at');
                $table->timestamps();
                $table->index(['metric_type', 'collected_at']);
                $table->index('collected_at');
            });
        }

        // Watchtower Incidents
        if (!Schema::hasTable('watchtower_incidents')) {
            Schema::create('watchtower_incidents', function (Blueprint $table) {
                $table->id();
                $table->string('incident_type', 50);
                $table->string('severity', 20);
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->string('service', 50)->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['severity', 'resolved_at']);
            });
        }

        // Watchtower Alert Rules
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
        Schema::dropIfExists('watchtower_incidents');
        Schema::dropIfExists('watchtower_metrics');
    }
};
