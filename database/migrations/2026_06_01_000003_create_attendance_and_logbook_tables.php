<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('student_grace_minutes')->default(10);
            $table->integer('teacher_grace_minutes')->default(10);
            $table->integer('auto_absent_after_minutes')->default(30);
            $table->timestamps();
        });

        Schema::create('biometric_device_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('biometric_user_id');
            $table->enum('user_type', ['student', 'staff', 'teacher']);
            $table->dateTime('punch_time');
            $table->enum('punch_type', ['in', 'out']);
            $table->string('device_id')->nullable();
            $table->json('raw_payload')->nullable();
            $table->enum('processed_status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('processing_message')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('biometric_device_log_id')->nullable();
            $table->date('attendance_date');
            $table->time('scheduled_start_time')->nullable();
            $table->time('scheduled_end_time')->nullable();
            $table->time('actual_in_time')->nullable();
            $table->time('actual_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('absent');
            $table->enum('source', ['manual', 'biometric'])->default('manual');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('biometric_device_log_id')->references('id')->on('biometric_device_logs')->nullOnDelete();
            $table->unique(['student_id', 'batch_id', 'subject_id', 'attendance_date'], 'student_attendance_unique');
        });

        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('attendance_date');
            $table->time('first_in_time')->nullable();
            $table->time('last_out_time')->nullable();
            $table->integer('worked_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('absent');
            $table->enum('source', ['manual', 'biometric'])->default('manual');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->foreign('staff_id')->references('id')->on('staff')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('faculty_log_books', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->date('lecture_date');
            $table->time('scheduled_start_time')->nullable();
            $table->time('scheduled_end_time')->nullable();
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->string('topic_taught')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('log_status', ['draft', 'submitted', 'missed', 'late_entry'])->default('draft');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('scheduled_minutes')->default(0);
            $table->integer('salary_minutes')->default(0);
            $table->boolean('is_salary_eligible')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->nullOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_log_books');
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('biometric_device_logs');
        Schema::dropIfExists('attendance_settings');
    }
};
