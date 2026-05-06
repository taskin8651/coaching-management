<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnquiriesTable extends Migration
{
    public function up()
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();

            $table->string('student_name');
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('email')->nullable();

            $table->string('class_name')->nullable();
            $table->string('school_name')->nullable();

            $table->string('source')->nullable();

            $table->date('enquiry_date')->nullable();
            $table->date('next_follow_up_date')->nullable();

            $table->enum('status', [
                'new',
                'follow_up',
                'interested',
                'not_interested',
                'converted',
                'rejected'
            ])->default('new');

            $table->longText('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->nullOnDelete();

            $table->foreign('assigned_to_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enquiries');
    }
}