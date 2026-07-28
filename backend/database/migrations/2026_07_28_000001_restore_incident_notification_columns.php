<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restores columns that the codebase requires on incident_notifications but
 * that no migration ever defined. They existed only in the dev database and
 * were lost when it was rebuilt from the migration chain.
 *
 * Column set derived from actual consumers:
 *   NotificationDispatched  - user_id, type, category, title, body, action_url, read_at
 *   NotificationFeedService - read, incident_id (via whereHas('incident')), created_at
 *   NotificationService     - viewed_at
 *   AnalyticsService / NotificationMonitorService - delivery_channel, status
 *   TrustedContactNotificationListener - message, metadata, resolved_at
 *   Model $fillable         - trusted_contact_id, delivered_at
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('incident_id')->nullable();
            $table->unsignedBigInteger('trusted_contact_id')->nullable();

            $table->string('type')->nullable();
            $table->string('category')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->text('message')->nullable();
            $table->string('action_url')->nullable();
            $table->json('metadata')->nullable();

            $table->string('delivery_channel')->nullable();
            $table->string('status')->nullable();

            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('viewed_at')->nullable();

            $table->index('user_id');
            $table->index('incident_id');
            $table->index(['user_id', 'read_at']);
            $table->index(['category', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['incident_id']);
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['category', 'type']);

            $table->dropColumn([
                'user_id', 'incident_id', 'trusted_contact_id',
                'type', 'category', 'title', 'body', 'message',
                'action_url', 'metadata',
                'delivery_channel', 'status',
                'read', 'read_at', 'resolved_at', 'delivered_at', 'viewed_at',
            ]);
        });
    }
};
