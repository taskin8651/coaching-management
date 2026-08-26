<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concessions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('student_fee_ledger_id');
            $table->unsignedBigInteger('student_id');

            $table->string('type');
            $table->enum('amount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();

            $table->text('reason')->nullable();

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by_id')->nullable();
            $table->date('approval_date')->nullable();

            $table->text('remarks')->nullable();
            $table->enum('status', ['active', 'cancelled'])->default('active');

            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('student_fee_ledger_id')->references('id')->on('student_fee_ledgers')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('approved_by_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concessions');
    }
};
