<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->longText('overall_remark')->nullable()->after('remarks');
            $table->unsignedBigInteger('remark_user_id')->nullable()->after('overall_remark');
            $table->timestamp('remark_updated_at')->nullable()->after('remark_user_id');
            $table->foreign('remark_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropForeign(['remark_user_id']);
            $table->dropColumn(['overall_remark', 'remark_user_id', 'remark_updated_at']);
        });
    }
};
