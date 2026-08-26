<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structure_installments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('fee_structure_id');
            $table->unsignedBigInteger('fee_account_id');

            $table->string('title');
            $table->unsignedInteger('sequence')->default(0);

            $table->enum('amount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();

            $table->date('due_date')->nullable();

            $table->timestamps();

            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->cascadeOnDelete();
            $table->foreign('fee_account_id')->references('id')->on('fee_accounts')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structure_installments');
    }
};
