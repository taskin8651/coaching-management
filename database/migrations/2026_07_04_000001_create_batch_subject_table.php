<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_subject', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('batches')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->unique(['batch_id', 'subject_id'], 'batch_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_subject');
    }
};
