<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite requires table rebuild to add foreign keys to existing columns
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement('ALTER TABLE safety_incidents RENAME TO _safety_incidents_old;');
            
            DB::statement('CREATE TABLE safety_incidents (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                user_id INTEGER NOT NULL,
                location_lat TEXT,
                location_lng TEXT,
                location_accuracy INTEGER,
                battery_level INTEGER,
                resolved_at DATETIME,
                type TEXT,
                status TEXT,
                message TEXT,
                escalated_at DATETIME,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
            );');
            
            DB::statement('INSERT INTO safety_incidents SELECT * FROM _safety_incidents_old;');
            DB::statement('DROP TABLE _safety_incidents_old;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // Standard approach for MySQL/PostgreSQL
            Schema::table('safety_incidents', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off;');
            DB::statement('ALTER TABLE safety_incidents RENAME TO _safety_incidents_old;');
            
            DB::statement('CREATE TABLE safety_incidents (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                user_id INTEGER,
                location_lat TEXT,
                location_lng TEXT,
                location_accuracy INTEGER,
                battery_level INTEGER,
                resolved_at DATETIME,
                type TEXT,
                status TEXT,
                message TEXT,
                escalated_at DATETIME
            );');
            
            DB::statement('INSERT INTO safety_incidents SELECT * FROM _safety_incidents_old;');
            DB::statement('DROP TABLE _safety_incidents_old;');
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            Schema::table('safety_incidents', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }
    }
};
