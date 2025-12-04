<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix programs that have incorrect status.
     * Programs should always be 'template' status - individual user instances
     * are tracked in the active_programs table.
     */
    public function up(): void
    {
        // Update all programs that don't have 'template' status back to 'template'
        // Programs should always remain as templates so multiple users can start them
        DB::table('programs')
            ->where('status', '!=', 'template')
            ->update(['status' => 'template']);
    }

    /**
     * Reverse the migrations.
     * 
     * Note: We can't reverse this migration as we don't know what the original
     * status values were. This is a data correction migration.
     */
    public function down(): void
    {
        // Cannot reverse - this is a data correction migration
        // The original incorrect status values are unknown
    }
};
