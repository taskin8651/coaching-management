<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->boolean('teachers_can_mark_attendance')->default(false);
        });
    }

    public function down()
    {
        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropColumn('teachers_can_mark_attendance');
        });
    }
};
