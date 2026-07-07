<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();

            $table->string('qualification')->nullable();
            $table->string('experience')->nullable();
            $table->string('subject_specialization')->nullable();

            $table->longText('address')->nullable();

            $table->decimal('salary', 12, 2)->default(0);
            $table->date('joining_date')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teachers');
    }
}