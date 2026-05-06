<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('paid_by_id')->nullable();

            $table->string('employee_type')->nullable(); // teacher, staff, manager
            $table->string('salary_month')->nullable(); // 2026-05
            $table->string('slip_no')->nullable()->unique();

            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);

            $table->enum('payment_mode', [
                'cash',
                'upi',
                'bank_transfer',
                'cheque',
                'card',
                'other'
            ])->default('cash');

            $table->date('payment_date')->nullable();

            $table->enum('payment_status', [
                'paid',
                'partial',
                'due',
                'cancelled'
            ])->default('due');

            $table->longText('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->foreign('staff_id')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('paid_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salary_payments');
    }
}