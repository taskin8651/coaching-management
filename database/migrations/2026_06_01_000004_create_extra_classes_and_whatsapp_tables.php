<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->date('class_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->integer('salary_minutes')->default(0);
            $table->decimal('salary_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->nullOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_provider')->nullable();
            $table->string('api_base_url')->nullable();
            $table->text('api_key')->nullable();
            $table->string('sender_number')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->timestamps();
        });

        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('guardian_number')->nullable();
            $table->string('module_name')->nullable();
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->longText('response')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
        Schema::dropIfExists('whatsapp_settings');
        Schema::dropIfExists('extra_classes');
    }
};
