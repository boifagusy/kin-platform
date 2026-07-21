<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            $table->foreignId('resolved_by_user_id')->nullable()->after('resolved_at')
                ->constrained('users')->nullOnDelete();
            $table->string('resolved_by_role')->nullable()->after('resolved_by_user_id');
            $table->text('resolution_note')->nullable()->after('resolved_by_role');
        });
    }

    public function down(): void
    {
        Schema::table('safety_incidents', function (Blueprint $table) {
            $table->dropForeign(['resolved_by_user_id']);
            $table->dropColumn(['resolved_by_user_id', 'resolved_by_role', 'resolution_note']);
        });
    }
};
