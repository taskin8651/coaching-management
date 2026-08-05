<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamSelfAssessmentsTable extends Migration
{
    public function up()
    {
        Schema::create('exam_self_assessments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('student_id');

            $table->decimal('expected_marks', 8, 2)->nullable();
            $table->enum('preparation_status', ['not_prepared', 'partially_prepared', 'well_prepared'])->nullable();
            $table->longText('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();

            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_self_assessments');
    }
}
