<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sos_events', function (Blueprint $table) {
            $table->decimal('accuracy', 8, 2)->nullable()->after('longitude');
            $table->unsignedTinyInteger('battery_level')->nullable()->after('accuracy');
        });
    }

    public function down(): void
    {
        Schema::table('sos_events', function (Blueprint $table) {
            $table->dropColumn(['accuracy', 'battery_level']);
        });
    }
};
