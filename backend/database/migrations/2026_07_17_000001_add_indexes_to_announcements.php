<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->index('status');
            $table->index('target_platform');
            $table->index('starts_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['target_platform']);
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['expires_at']);
        });
    }
};
