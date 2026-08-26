<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_contacts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('standard')->nullable();
            $table->string('school_name')->nullable();

            $table->string('mobile');
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();

            $table->string('guardian_name')->nullable();
            $table->string('guardian_mobile')->nullable();
            $table->string('guardian_email')->nullable();

            $table->string('city')->nullable();
            $table->string('area')->nullable();

            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('created_by_id')->references('id')->on('users')->nullOnDelete();

            $table->index('mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_contacts');
    }
};
