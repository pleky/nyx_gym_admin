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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->restrictOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_for');
            $table->string('method');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });


        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payment_status CHECK (status IN ('PAID', 'PENDING', 'REFUNDED', 'CANCELLED'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payment_method CHECK (method IN ('CASH', 'DEBIT_CARD', 'BANK_TRANSFER', 'E_WALLET'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_amount CHECK (amount >= 0)");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payment_for CHECK (payment_for IN ('MEMBERSHIP', 'CLASS', 'RETAIL'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
