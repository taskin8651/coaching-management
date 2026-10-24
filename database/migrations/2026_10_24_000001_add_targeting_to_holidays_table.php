<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
            $table->string('target_mode')->nullable()->after('category');
            $table->json('audiences')->nullable()->after('target_mode');
        });

        Schema::create('holiday_targets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('holiday_id');
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id');
            $table->timestamps();
            $table->foreign('holiday_id')->references('id')->on('holidays')->cascadeOnDelete();
            $table->unique(['holiday_id', 'target_type', 'target_id'], 'holiday_target_unique');
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_targets');
        Schema::table('holidays', fn (Blueprint $table) => $table->dropColumn(['category', 'target_mode', 'audiences']));
    }
};
