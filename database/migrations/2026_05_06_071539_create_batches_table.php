<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTable extends Migration
{
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();

            $table->string('name');
            $table->string('batch_code')->nullable()->unique();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->integer('max_students')->nullable();
            $table->longText('description')->nullable();

            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');

            $table->timestamps();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('batches');
    }
}