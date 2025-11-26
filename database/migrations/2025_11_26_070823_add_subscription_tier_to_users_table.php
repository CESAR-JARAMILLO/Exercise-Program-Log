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
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_tier')
                ->default('free')
                ->after('timezone')
                ->index();
        });

        // Update all existing users to 'free' tier
        DB::table('users')->update(['subscription_tier' => 'free']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['subscription_tier']);
            $table->dropColumn('subscription_tier');
        });
    }
};
