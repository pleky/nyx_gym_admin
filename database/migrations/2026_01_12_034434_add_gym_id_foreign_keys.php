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
        // set gym_id not nullable after data migration
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('gym_id')->nullable(false)->change();
            $table->foreign('gym_id')->references('id')->on('gyms')->restrictOnDelete();
            $table->index('gym_id');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->unsignedInteger('gym_id')->nullable(false)->change();
            $table->foreign('gym_id')->references('id')->on('gyms')->restrictOnDelete();
            $table->index('gym_id');
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->unsignedInteger('gym_id')->nullable(false)->change();
            $table->foreign('gym_id')->references('id')->on('gyms')->restrictOnDelete();
            $table->index('gym_id');
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->unsignedInteger('gym_id')->nullable(false)->change();
            $table->foreign('gym_id')->references('id')->on('gyms')->restrictOnDelete();
            $table->index('gym_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['gym_id']);
            $table->unsignedInteger('gym_id')->nullable()->change();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['gym_id']);
            $table->unsignedInteger('gym_id')->nullable()->change();
        });

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropForeign(['gym_id']);
            $table->unsignedInteger('gym_id')->nullable()->change();
        });

        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropForeign(['gym_id']);
            $table->unsignedInteger('gym_id')->nullable()->change();
        });
    }
};
