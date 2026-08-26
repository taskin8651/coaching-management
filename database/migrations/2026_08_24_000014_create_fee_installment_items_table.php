<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_installment_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('fee_installment_id');
            $table->unsignedBigInteger('fee_head_id');

            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('fee_installment_id')->references('id')->on('fee_installments')->cascadeOnDelete();
            $table->foreign('fee_head_id')->references('id')->on('fee_heads')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_installment_items');
    }
};
