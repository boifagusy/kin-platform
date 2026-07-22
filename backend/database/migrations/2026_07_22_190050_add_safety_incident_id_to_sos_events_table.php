<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sos_events', function (Blueprint $table) {
            $table->foreignId('safety_incident_id')->nullable()->constrained()->onDelete('set null')->after('is_duress');
            $table->index('safety_incident_id');
        });
    }

    public function down(): void
    {
        Schema::table('sos_events', function (Blueprint $table) {
            $table->dropIndex(['safety_incident_id']);
            $table->dropForeign(['safety_incident_id']);
            $table->dropColumn('safety_incident_id');
        });
    }
};
