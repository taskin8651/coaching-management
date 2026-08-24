<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('admissions');

        Permission::whereIn('title', [
            'admission_create',
            'admission_edit',
            'admission_show',
            'admission_delete',
            'admission_access',
        ])->delete();
    }

    public function down(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('admission_no')->nullable()->unique();

            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('enquiry_id')->nullable();

            $table->date('admission_date')->nullable();

            $table->string('previous_school')->nullable();
            $table->string('previous_class')->nullable();
            $table->string('qualification')->nullable();

            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_whatsapp')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('emergency_contact')->nullable();

            $table->decimal('course_fee', 12, 2)->default(0);
            $table->decimal('admission_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('payable_amount', 12, 2)->default(0);

            $table->string('admission_source')->nullable();

            $table->enum('status', [
                'pending',
                'confirmed',
                'rejected',
                'cancelled',
                'completed',
            ])->default('pending');

            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->nullOnDelete();
            $table->foreign('enquiry_id')->references('id')->on('enquiries')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
