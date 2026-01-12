<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
          $table->unsignedInteger('gym_id')->nullable();
        });

        Schema::table('members', function (Blueprint $table) {
          $table->unsignedInteger('gym_id')->nullable();
        });

        Schema::table('membership_plans', function (Blueprint $table) {
          $table->unsignedInteger('gym_id')->nullable();
        });

        Schema::table('check_ins', function (Blueprint $table) {
          $table->unsignedInteger('gym_id')->nullable();
        });

        DB::statement('UPDATE users SET gym_id = 1'); 
        DB::statement('UPDATE members SET gym_id = 1');
        DB::statement('UPDATE membership_plans SET gym_id = 1');
        DB::statement('UPDATE check_ins SET gym_id = 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
          $table->dropColumn('gym_id');
        });

        Schema::table('members', function (Blueprint $table) {
          $table->dropColumn('gym_id');
        });

        Schema::table('membership_plans', function (Blueprint $table) {
          $table->dropColumn('gym_id');
        });

        Schema::table('check_ins', function (Blueprint $table) {
          $table->dropColumn('gym_id');
        });
    }
};
