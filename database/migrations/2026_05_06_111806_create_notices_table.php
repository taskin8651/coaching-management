<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticesTable extends Migration
{
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->string('title');
            $table->string('notice_type')->nullable();

            $table->enum('target_audience', [
                'all',
                'students',
                'teachers',
                'staff',
                'managers',
                'branch',
                'course',
                'batch'
            ])->default('all');

            $table->longText('description')->nullable();

            $table->date('publish_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->enum('status', ['draft', 'published', 'inactive'])->default('published');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches')->nullOnDelete();
            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notices');
    }
}