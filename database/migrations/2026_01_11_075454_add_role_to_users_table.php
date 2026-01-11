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
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom role (OWNER atau STAFF)
            $table->enum('role', ['OWNER', 'STAFF'])
            ->after('email')
            ->comment('Role of the user: OWNER can manage everything, STAFF can manage members');

            $table->string('phone', 20)
            ->nullable()
            ->unique()
            ->after('email');

            $table->enum('status', ['ACTIVE', 'INACTIVE'])
            ->default('ACTIVE')
            ->after('password');

            $table->softDeletes()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'status']);
        });
    }
};
