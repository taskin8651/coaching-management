<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['bank', 'cash', 'other'])->default('cash');

            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('upi_id')->nullable();

            $table->boolean('gst_applicable')->default(false);
            $table->string('gst_number')->nullable();

            $table->text('receipt_address')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_accounts');
    }
};
