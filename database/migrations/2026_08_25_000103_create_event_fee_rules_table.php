<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_fee_rules', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('event_id');

            $table->enum('rule_type', ['karmayoga_student', 'external_student', 'group', 'early_bird']);
            $table->string('label')->nullable();
            $table->decimal('amount', 12, 2);

            $table->unsignedInteger('min_group_size')->nullable();
            $table->date('valid_until')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_fee_rules');
    }
};
