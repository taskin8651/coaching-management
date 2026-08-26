<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payment_allocations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('fee_payment_id');
            $table->unsignedBigInteger('fee_installment_id');
            $table->unsignedBigInteger('fee_installment_item_id')->nullable();

            $table->decimal('amount', 12, 2);

            $table->timestamps();

            $table->foreign('fee_payment_id')->references('id')->on('fee_payments')->cascadeOnDelete();
            $table->foreign('fee_installment_id')->references('id')->on('fee_installments')->cascadeOnDelete();
            $table->foreign('fee_installment_item_id')->references('id')->on('fee_installment_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payment_allocations');
    }
};
