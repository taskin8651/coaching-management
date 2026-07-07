<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('paid_by_id')->nullable();

            $table->string('title');
            $table->string('category')->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->date('expense_date')->nullable();

            $table->enum('payment_mode', [
                'cash',
                'upi',
                'bank_transfer',
                'cheque',
                'card',
                'other'
            ])->default('cash');

            $table->string('vendor_name')->nullable();
            $table->string('bill_no')->nullable();

            $table->enum('status', [
                'paid',
                'pending',
                'cancelled'
            ])->default('paid');

            $table->longText('remarks')->nullable();

            $table->timestamps();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->foreign('paid_by_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}