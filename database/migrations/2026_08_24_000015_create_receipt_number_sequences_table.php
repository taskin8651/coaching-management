<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_number_sequences', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('academic_year');
            $table->unsignedInteger('last_number')->default(0);

            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();

            $table->unique(['branch_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_number_sequences');
    }
};
