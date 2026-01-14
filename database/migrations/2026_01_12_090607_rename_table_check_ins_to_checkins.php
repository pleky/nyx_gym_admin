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
        DB::statement('ALTER TABLE check_ins RENAME TO checkins');

        Schema::table('checkins', function (Blueprint $table) {
            $table->renameColumn('checkin_at', 'checked_in_at');
            $table->dropColumn('created_by');
            $table->dropColumn('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->renameColumn('checked_in_at', 'checkin_at');
        });
    
        DB::statement('ALTER TABLE checkins RENAME TO check_ins');
    }
};
