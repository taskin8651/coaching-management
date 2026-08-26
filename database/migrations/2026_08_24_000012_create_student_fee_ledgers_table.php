<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fee_ledgers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->unsignedInteger('fee_structure_version')->default(1);

            $table->decimal('net_payable', 12, 2)->default(0);
            $table->decimal('concession_total', 12, 2)->default(0);
            $table->decimal('paid_till_date', 12, 2)->default(0);
            $table->decimal('outstanding_amount', 12, 2)->default(0);

            $table->unsignedBigInteger('assigned_by_id')->nullable();
            $table->timestamp('assigned_at')->nullable();

            $table->enum('status', ['active', 'closed', 'cancelled'])->default('active');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->restrictOnDelete();
            $table->foreign('assigned_by_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['student_id', 'fee_structure_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fee_ledgers');
    }
};
