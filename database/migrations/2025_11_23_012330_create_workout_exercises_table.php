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
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('day_exercise_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Can override target name
            $table->string('type'); // strength, cardio, flexibility, other
            $table->integer('sets_actual')->nullable();
            $table->integer('reps_actual')->nullable();
            $table->decimal('weight_actual', 8, 2)->nullable();
            $table->decimal('distance_actual', 8, 2)->nullable();
            $table->integer('time_seconds_actual')->nullable();
            $table->text('notes')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
    }
};
