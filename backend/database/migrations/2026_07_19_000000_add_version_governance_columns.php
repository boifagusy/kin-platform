<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('min_version_code');
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('admin_users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status', 'approved_by', 'reviewed_at']);
        });
    }
};
