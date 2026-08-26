<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('name');
            $table->string('code')->unique();
            $table->string('event_type')->nullable();
            $table->text('description')->nullable();
            $table->string('venue')->nullable();

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('registration_start_date')->nullable();
            $table->date('registration_end_date')->nullable();

            $table->decimal('base_fee', 12, 2)->default(0);
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('external_enrollment_allowed')->default(true);

            $table->enum('status', ['draft', 'open', 'closed', 'cancelled'])->default('draft');

            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
