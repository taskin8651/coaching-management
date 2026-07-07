<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamResultsTable extends Migration
{
    public function up()
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('exam_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();

            $table->decimal('marks_obtained', 8, 2)->default(0);
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->decimal('percentage', 8, 2)->default(0);

            $table->enum('result_status', ['pass', 'fail', 'absent'])->default('fail');

            $table->integer('rank')->nullable();

            $table->longText('remarks')->nullable();

            $table->timestamps();

            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();

            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_results');
    }
}