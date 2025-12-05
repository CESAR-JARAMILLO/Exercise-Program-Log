<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change description from VARCHAR(255) to TEXT to support longer descriptions
        // Notes is already TEXT, so no change needed
        
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // MySQL/MariaDB syntax - production database
            DB::statement('ALTER TABLE programs MODIFY description TEXT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY, but for local development
            // we can skip this since SQLite TEXT has no length limit anyway
            // The column will effectively work as TEXT in SQLite
            // This migration is primarily for MySQL production databases
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // MySQL/MariaDB syntax - production database
            DB::statement('ALTER TABLE programs MODIFY description VARCHAR(255) NULL');
        }
        // SQLite: no action needed as it doesn't enforce VARCHAR length limits
    }
};
