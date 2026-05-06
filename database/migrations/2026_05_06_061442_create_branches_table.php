<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('branch_code')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->longText('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();

            $table->unsignedBigInteger('manager_id')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branches');
    }
}