<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();

            $table->string('student_code')->nullable()->unique();

            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();

            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->longText('address')->nullable();

            $table->string('school_name')->nullable();
            $table->string('class_name')->nullable();

            $table->date('admission_date')->nullable();

            $table->enum('status', ['active', 'inactive', 'completed', 'dropped'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->nullOnDelete();

            $table->foreign('batch_id')
                ->references('id')
                ->on('batches')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
}