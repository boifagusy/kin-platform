<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('safety_events', function (Blueprint $table) {
            // Add missing columns
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            $table->string('event_type')->after('user_id');
            $table->string('correlation_id')->nullable()->after('event_type');
            
            // Location fields
            $table->decimal('location_lat', 10, 8)->nullable()->after('correlation_id');
            $table->decimal('location_lng', 11, 8)->nullable()->after('location_lat');
            $table->float('location_confidence', 3, 2)->nullable()->after('location_lng');
            
            // Device status
            $table->integer('battery_level')->nullable()->after('location_confidence');
            $table->string('network_status')->nullable()->after('battery_level');
            $table->string('device_status')->nullable()->after('network_status');
            
            // Guardian fields
            $table->boolean('trusted_contacts_notified')->default(false)->after('device_status');
            $table->timestamp('guardian_acknowledged_at')->nullable()->after('trusted_contacts_notified');
            $table->timestamp('resolved_at')->nullable()->after('guardian_acknowledged_at');
            
            // Metadata
            $table->json('metadata')->nullable()->after('resolved_at');
            
            // Indexes
            $table->index(['user_id', 'event_type']);
            $table->index('created_at');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::table('safety_events', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'event_type', 'correlation_id',
                'location_lat', 'location_lng', 'location_confidence',
                'battery_level', 'network_status', 'device_status',
                'trusted_contacts_notified', 'guardian_acknowledged_at',
                'resolved_at', 'metadata'
            ]);
        });
    }
};
