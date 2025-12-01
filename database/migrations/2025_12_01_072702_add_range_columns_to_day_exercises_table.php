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
        Schema::table('day_exercises', function (Blueprint $table) {
            // Add min/max columns for ranges
            $table->unsignedTinyInteger('sets_min')->nullable()->after('sets');
            $table->unsignedTinyInteger('sets_max')->nullable()->after('sets_min');
            $table->unsignedTinyInteger('reps_min')->nullable()->after('reps');
            $table->unsignedTinyInteger('reps_max')->nullable()->after('reps_min');
            $table->decimal('weight_min', 8, 2)->nullable()->after('weight');
            $table->decimal('weight_max', 8, 2)->nullable()->after('weight_min');
            $table->decimal('distance_min', 8, 2)->nullable()->after('distance');
            $table->decimal('distance_max', 8, 2)->nullable()->after('distance_min');
            $table->unsignedInteger('time_seconds_min')->nullable()->after('time_seconds');
            $table->unsignedInteger('time_seconds_max')->nullable()->after('time_seconds_min');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_exercises', function (Blueprint $table) {
            $table->dropColumn([
                'sets_min', 'sets_max',
                'reps_min', 'reps_max',
                'weight_min', 'weight_max',
                'distance_min', 'distance_max',
                'time_seconds_min', 'time_seconds_max',
            ]);
        });
    }
};
