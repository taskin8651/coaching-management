<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('student_fee_ledger_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_payment_id')->nullable();
            $table->unsignedBigInteger('fee_installment_id')->nullable();
            $table->unsignedBigInteger('fee_account_id');

            $table->decimal('amount', 12, 2);
            $table->enum('mode', ['cash', 'upi', 'bank_transfer', 'cheque', 'card', 'other'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->date('refund_date');

            $table->text('reason');
            $table->text('remarks')->nullable();

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by_id')->nullable();
            $table->date('approval_date')->nullable();

            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('completed_by_id')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('student_fee_ledger_id')->references('id')->on('student_fee_ledgers')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->nullOnDelete();
            $table->foreign('fee_installment_id')->references('id')->on('fee_installments')->nullOnDelete();
            $table->foreign('fee_account_id')->references('id')->on('fee_accounts')->restrictOnDelete();
            $table->foreign('approved_by_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('completed_by_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
