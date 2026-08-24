<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_timetables', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('day_of_week')->nullable();
            $table->date('schedule_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');

            $table->string('location')->nullable();
            $table->enum('status', ['scheduled', 'cancelled'])->default('scheduled');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staff')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_timetables');
    }
};
