<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('workout_logs')) {
            Schema::create('workout_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('active_program_id')->constrained()->cascadeOnDelete();
                $table->foreignId('program_day_id')->constrained()->cascadeOnDelete();
                $table->date('workout_date');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                // Prevent duplicate logs for same day (user can edit existing)
                $table->unique(['active_program_id', 'program_day_id', 'workout_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
    }
};
