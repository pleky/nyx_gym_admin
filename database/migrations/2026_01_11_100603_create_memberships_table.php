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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('membership_plan_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 10)->default('ACTIVE');
            $table->decimal('price_paid', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("
            ALTER TABLE memberships
            ADD CONSTRAINT status_check
            CHECK (status IN ('ACTIVE', 'EXPIRED', 'CANCELLED'))        
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
