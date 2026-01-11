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

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_id', 10)->unique();
            $table->string('name');
            $table->string('phone')->unique()->nullable();
            $table->string('gender', 1);
            $table->date('date_of_birth')->nullable();
            $table->string('status', 10)->default('ACTIVE');
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE members ADD CONSTRAINT chk_member_id_format CHECK (member_id ~ '^MBR-[0-9]{4}$')");

        DB::statement("
            ALTER TABLE members
            ADD CONSTRAINT gender_check
            CHECK (gender IN ('M', 'F', 'O'))        
        ");

        DB::statement("
            ALTER TABLE members
            ADD CONSTRAINT status_check
            CHECK (status IN ('ACTIVE', 'INACTIVE'))        
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
