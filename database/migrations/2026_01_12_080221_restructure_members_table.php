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
            
            // 3. Add indexes for query performance
            $table->index('status'); // WHERE status = 'ACTIVE' queries
            $table->index('deleted_at'); // Soft delete queries optimization
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Drop indexes first (dependencies)
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['status']);
            
            // Drop email column
            $table->dropUnique(['email']); // Drop unique constraint
            $table->dropColumn('email');
            
            // Rename column back
            $table->renameColumn('full_name', 'name');
        });
    }
};
