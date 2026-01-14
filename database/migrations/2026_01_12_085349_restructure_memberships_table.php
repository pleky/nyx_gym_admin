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
        Schema::table('memberships', function (Blueprint $table) {
           $table->dropColumn('price_paid');
           $table->boolean('auto_renew')->default(false)->after('end_date');
           $table->index(['status', 'deleted_at']);
           $table->foreignId('gym_id')->constrained()->restrictOnDelete()->after('member_id');
        });

        DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS status_check');

        DB::statement("ALTER TABLE memberships ADD CONSTRAINT status_check CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED', 'PENDING_RENEWAL'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->decimal('price_paid', 10, 2)->after('end_date');
            $table->dropColumn('auto_renew');
            $table->dropIndex(['status', 'deleted_at']);
            $table->dropForeign(['gym_id']);
        });

        DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS status_check');

        DB::statement("ALTER TABLE memberships ADD CONSTRAINT status_check CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED'))");
    }
};
