<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('day_of_week')->nullable();
            $table->date('schedule_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->enum('status', ['scheduled', 'changed', 'cancelled'])->default('scheduled');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });

        Schema::create('timetable_substitutions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('timetable_id');
            $table->unsignedBigInteger('original_teacher_id')->nullable();
            $table->unsignedBigInteger('substitute_teacher_id')->nullable();
            $table->date('substitution_date');
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->text('change_note')->nullable();
            $table->timestamps();

            $table->foreign('timetable_id')->references('id')->on('timetables')->cascadeOnDelete();
            $table->foreign('original_teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->foreign('substitute_teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->foreign('changed_by_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('homeworks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('title');
            $table->longText('details')->nullable();
            $table->date('homework_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
        });

        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('homework_id');
            $table->unsignedBigInteger('student_id');
            $table->enum('status', ['pending', 'submitted', 'completed', 'incomplete'])->default('pending');
            $table->dateTime('submitted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('homework_id')->references('id')->on('homeworks')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->unique(['homework_id', 'student_id', 'deleted_at'], 'homework_student_unique');
        });

        Schema::create('student_remarks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->enum('remark_type', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->date('remark_date');
            $table->string('title')->nullable();
            $table->text('remark');
            $table->boolean('visible_to_parent')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('teacher_id')->references('id')->on('teachers')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('reported_by_id')->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->string('title');
            $table->string('category')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('description')->nullable();
            $table->text('repair_notes')->nullable();
            $table->date('reported_date')->nullable();
            $table->date('resolved_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('reported_by_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('expense_id')->references('id')->on('expenses')->nullOnDelete();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->integer('opening_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('low_stock_level')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inventory_item_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->enum('transaction_type', ['stock_in', 'stock_out', 'adjustment'])->default('stock_in');
            $table->integer('quantity')->default(0);
            $table->date('transaction_date')->nullable();
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('expense_id')->references('id')->on('expenses')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('fee_installments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id')->nullable();
            $table->string('title');
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->dateTime('reminded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
        });

        Schema::create('report_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('total_marks', 10, 2)->default(0);
            $table->decimal('marks_obtained', 10, 2)->default(0);
            $table->decimal('percentage', 8, 2)->default(0);
            $table->string('grade')->nullable();
            $table->integer('rank')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('published_to_parent')->default(false);
            $table->date('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('fee_installments');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('student_remarks');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homeworks');
        Schema::dropIfExists('timetable_substitutions');
        Schema::dropIfExists('timetables');
    }
};
