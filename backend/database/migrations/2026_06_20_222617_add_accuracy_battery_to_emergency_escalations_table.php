<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emergency_escalations', function (Blueprint $table) {
            $table->decimal('location_accuracy', 8, 2)->nullable()->after('location_lng');
            $table->unsignedTinyInteger('battery_level')->nullable()->after('location_accuracy');
        });
    }

    public function down(): void
    {
        Schema::table('emergency_escalations', function (Blueprint $table) {
            $table->dropColumn(['location_accuracy', 'battery_level']);
        });
    }
};
