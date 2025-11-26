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
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('trainer_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->index('trainer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['trainer_id']);
            $table->dropIndex(['trainer_id']);
            $table->dropColumn('trainer_id');
        });
    }
};
