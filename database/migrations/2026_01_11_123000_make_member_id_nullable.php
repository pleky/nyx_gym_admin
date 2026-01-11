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
        // Make member_id nullable so model can set it after create
        DB::statement("ALTER TABLE members ALTER COLUMN member_id DROP NOT NULL");

        // Recreate check constraint to allow NULL values
        DB::statement('ALTER TABLE members DROP CONSTRAINT IF EXISTS chk_member_id_format');
        DB::statement("ALTER TABLE members ADD CONSTRAINT chk_member_id_format CHECK (member_id IS NULL OR member_id ~ '^MBR-[0-9]{4}$')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert member_id to NOT NULL (only if all rows have member_id)
        DB::statement('ALTER TABLE members DROP CONSTRAINT IF EXISTS chk_member_id_format');
        DB::statement("ALTER TABLE members ADD CONSTRAINT chk_member_id_format CHECK (member_id ~ '^MBR-[0-9]{4}$')");
        DB::statement('ALTER TABLE members ALTER COLUMN member_id SET NOT NULL');
    }
};
