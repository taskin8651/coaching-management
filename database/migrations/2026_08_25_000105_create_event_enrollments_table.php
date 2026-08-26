<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_enrollments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('external_contact_id')->nullable();
            $table->enum('participant_type', ['student', 'external']);

            $table->unsignedInteger('group_size')->nullable();
            $table->string('fee_rule_label')->nullable();
            $table->decimal('fee_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'complimentary', 'refunded'])->default('unpaid');

            $table->date('enrollment_date');
            $table->enum('status', ['registered', 'waitlisted', 'cancelled'])->default('registered');
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by_id')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->boolean('is_present')->nullable();
            $table->timestamp('attendance_marked_at')->nullable();
            $table->unsignedBigInteger('attendance_marked_by_id')->nullable();

            $table->string('certificate_number')->nullable();
            $table->enum('certificate_status', ['not_applicable', 'pending', 'issued'])->default('not_applicable');

            $table->unsignedBigInteger('enrolled_by_id')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->foreign('external_contact_id')->references('id')->on('external_contacts')->nullOnDelete();
            $table->foreign('cancelled_by_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('attendance_marked_by_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('enrolled_by_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['event_id', 'student_id']);
            $table->index(['event_id', 'external_contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_enrollments');
    }
};
