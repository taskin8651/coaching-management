<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faculty_log_books', function (Blueprint $table) {
            if (! Schema::hasColumn('faculty_log_books', 'unique_key')) {
                $table->string('unique_key')->nullable()->unique()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('faculty_log_books', function (Blueprint $table) {
            if (Schema::hasColumn('faculty_log_books', 'unique_key')) {
                $table->dropUnique(['unique_key']);
                $table->dropColumn('unique_key');
            }
        });
    }
};
