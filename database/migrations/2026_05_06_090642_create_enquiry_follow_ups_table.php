<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnquiryFollowUpsTable extends Migration
{
    public function up()
    {
        Schema::create('enquiry_follow_ups', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('enquiry_id')->nullable();
            $table->unsignedBigInteger('followed_by_id')->nullable();

            $table->date('follow_up_date')->nullable();
            $table->string('follow_up_type')->nullable();

            $table->longText('response')->nullable();
            $table->date('next_follow_up_date')->nullable();

            $table->enum('status', [
                'new',
                'follow_up',
                'interested',
                'not_interested',
                'converted',
                'rejected'
            ])->default('follow_up');

            $table->longText('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('enquiry_id')
                ->references('id')
                ->on('enquiries')
                ->cascadeOnDelete();

            $table->foreign('followed_by_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enquiry_follow_ups');
    }
}