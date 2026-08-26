<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_credit_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('student_fee_ledger_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_payment_id')->nullable();
            $table->unsignedBigInteger('fee_installment_id')->nullable();

            $table->enum('type', ['credit', 'debit']);
            $table->enum('source', ['overpayment', 'applied_to_installment', 'refund', 'manual_adjustment']);
            $table->decimal('amount', 12, 2);

            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('student_fee_ledger_id')->references('id')->on('student_fee_ledgers')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->nullOnDelete();
            $table->foreign('fee_installment_id')->references('id')->on('fee_installments')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_credit_transactions');
    }
};
