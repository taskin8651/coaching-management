<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homeworks', function (Blueprint $table) {
            $table->unsignedBigInteger('faculty_log_book_id')->nullable()->unique()->after('teacher_id');
            $table->foreign('faculty_log_book_id')->references('id')->on('faculty_log_books')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('homeworks', function (Blueprint $table) {
            $table->dropForeign(['faculty_log_book_id']);
            $table->dropColumn('faculty_log_book_id');
        });
    }
};
