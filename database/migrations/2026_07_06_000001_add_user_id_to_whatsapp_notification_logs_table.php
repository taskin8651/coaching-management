<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_notification_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('student_id');
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_notification_logs', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
