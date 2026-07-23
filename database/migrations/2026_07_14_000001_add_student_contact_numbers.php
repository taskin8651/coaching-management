<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'father_phone')) {
                $table->string('father_phone')->nullable()->after('father_name');
            }

            if (! Schema::hasColumn('students', 'mother_phone')) {
                $table->string('mother_phone')->nullable()->after('mother_name');
            }

            if (! Schema::hasColumn('students', 'notification_phone')) {
                $table->string('notification_phone')->nullable()->after('guardian_whatsapp');
            }

            if (! Schema::hasColumn('students', 'student_personal_phone')) {
                $table->string('student_personal_phone')->nullable()->after('notification_phone');
            }
        });

        DB::table('students')
            ->whereNull('notification_phone')
            ->update([
                'notification_phone' => DB::raw('COALESCE(guardian_whatsapp, guardian_phone, phone, alternate_phone)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            foreach (['student_personal_phone', 'notification_phone', 'mother_phone', 'father_phone'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
