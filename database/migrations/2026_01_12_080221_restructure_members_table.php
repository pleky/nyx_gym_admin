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
        Schema::table('members', function (Blueprint $table) {
            // 1. Rename column: name → full_name
            $table->renameColumn('name', 'full_name');
            
            // 2. Add email column (nullable, unique)
            $table->string('email')->nullable()->unique()->after('phone');

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete()->after('id');

            // 3. Add indexes for query performance
            $table->index(['status', 'deleted_at', 'created_by']); // WHERE status = 'ACTIVE' queries

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // 1. Drop composite index first
            $table->dropIndex(['status', 'deleted_at', 'created_by']);
            
            // 2. Drop foreign key constraint
            $table->dropForeign(['created_by']);
            
            // 3. Drop columns
            $table->dropColumn(['created_by', 'email']);
            
            // 4. Rename column back
            $table->renameColumn('full_name', 'name');
            
            // 5. Recreate old indexes
            $table->index('status');
            $table->index('deleted_at');
        });
    }
};
