<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->string('registry_key')->nullable()->after('type');
            $table->string('trigger')->default('SERVER_EVENT')->after('registry_key');
            $table->integer('priority')->default(1)->after('trigger');
            $table->boolean('action_required')->default(false)->after('priority');
            $table->boolean('action_completed')->default(false)->after('action_required');
            $table->json('action_data')->nullable()->after('action_completed');
            $table->string('storage_policy')->default('SERVER_ONLY')->after('action_data');
            $table->string('sync_status')->default('SYNCED')->after('storage_policy');
            $table->timestamp('expires_at')->nullable()->after('sync_status');
            $table->string('lifecycle_state')->default('CREATED')->after('expires_at');
        });
    }

    public function down(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('This migration cannot be rolled back in production without data migration review.');
        }
        Schema::table('incident_notifications', function (Blueprint $table) {
            $table->dropColumn([
                'registry_key', 'trigger', 'priority', 'action_required',
                'action_completed', 'action_data', 'storage_policy',
                'sync_status', 'expires_at', 'lifecycle_state',
            ]);
        });
    }
};
