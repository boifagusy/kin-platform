<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add duress flag to SOS events.
     */
    public function up(): void
    {
        Schema::table('sos_events', function (Blueprint $table) {
            $table->boolean('is_duress')
                ->default(false)
                ->after('resolved_at');
        });
    }

    /**
     * Rollback duress flag.
     */
    public function down(): void
    {
        Schema::table('sos_events', function (Blueprint $table) {
            $table->dropColumn('is_duress');
        });
    }
};
