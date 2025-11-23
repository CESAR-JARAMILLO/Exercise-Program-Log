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
        Schema::create('active_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->date('started_at');
            $table->integer('current_week')->default(1);
            $table->integer('current_day')->default(1);
            $table->string('status')->default('active'); // active, completed
            $table->timestamps();
            
            // Allow multiple active programs per user, but prevent exact duplicates
            $table->index(['user_id', 'program_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_programs');
    }
};
