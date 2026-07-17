<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->index('is_active');
        });
        Schema::table('version_channels', function (Blueprint $table) {
            $table->index(['platform', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
        Schema::table('version_channels', function (Blueprint $table) {
            $table->dropIndex(['platform', 'enabled']);
        });
    }
};
