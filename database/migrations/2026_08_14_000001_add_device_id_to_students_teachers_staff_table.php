<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'device_id')) {
                $table->string('device_id')->nullable()->after('biometric_id');
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'device_id')) {
                $table->string('device_id')->nullable()->after('biometric_id');
            }
        });

        Schema::table('staff', function (Blueprint $table) {
            if (! Schema::hasColumn('staff', 'device_id')) {
                $table->string('device_id')->nullable()->after('biometric_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'device_id')) {
                $table->dropColumn('device_id');
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'device_id')) {
                $table->dropColumn('device_id');
            }
        });

        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'device_id')) {
                $table->dropColumn('device_id');
            }
        });
    }
};
