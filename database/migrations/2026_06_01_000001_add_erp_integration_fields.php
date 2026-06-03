<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'guardian_name')) {
                $table->string('guardian_name')->nullable()->after('mother_name');
            }
            if (! Schema::hasColumn('students', 'guardian_phone')) {
                $table->string('guardian_phone')->nullable()->after('guardian_name');
            }
            if (! Schema::hasColumn('students', 'guardian_whatsapp')) {
                $table->string('guardian_whatsapp')->nullable()->after('guardian_phone');
            }
            if (! Schema::hasColumn('students', 'emergency_contact')) {
                $table->string('emergency_contact')->nullable()->after('alternate_phone');
            }
            if (! Schema::hasColumn('students', 'biometric_id')) {
                $table->string('biometric_id')->nullable()->unique()->after('student_code');
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'biometric_id')) {
                $table->string('biometric_id')->nullable()->unique()->after('branch_id');
            }
            if (! Schema::hasColumn('teachers', 'salary_type')) {
                $table->string('salary_type')->default('monthly')->after('salary');
            }
            if (! Schema::hasColumn('teachers', 'minute_rate')) {
                $table->decimal('minute_rate', 10, 2)->default(0)->after('salary_type');
            }
        });

        Schema::table('staff', function (Blueprint $table) {
            if (! Schema::hasColumn('staff', 'biometric_id')) {
                $table->string('biometric_id')->nullable()->unique()->after('branch_id');
            }
            if (! Schema::hasColumn('staff', 'salary_type')) {
                $table->string('salary_type')->default('monthly')->after('salary');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'biometric_id')) {
                $table->string('biometric_id')->nullable()->unique()->after('email');
            }
        });

        Schema::table('salary_payments', function (Blueprint $table) {
            $columns = [
                'total_scheduled_lectures' => fn () => $table->integer('total_scheduled_lectures')->default(0)->after('due_amount'),
                'attended_lectures' => fn () => $table->integer('attended_lectures')->default(0)->after('total_scheduled_lectures'),
                'missed_lectures' => fn () => $table->integer('missed_lectures')->default(0)->after('attended_lectures'),
                'late_joined_lectures' => fn () => $table->integer('late_joined_lectures')->default(0)->after('missed_lectures'),
                'total_scheduled_minutes' => fn () => $table->integer('total_scheduled_minutes')->default(0)->after('late_joined_lectures'),
                'total_payable_regular_minutes' => fn () => $table->integer('total_payable_regular_minutes')->default(0)->after('total_scheduled_minutes'),
                'approved_extra_class_minutes' => fn () => $table->integer('approved_extra_class_minutes')->default(0)->after('total_payable_regular_minutes'),
                'deducted_minutes' => fn () => $table->integer('deducted_minutes')->default(0)->after('approved_extra_class_minutes'),
                'gross_salary' => fn () => $table->decimal('gross_salary', 12, 2)->default(0)->after('deducted_minutes'),
                'extra_class_amount' => fn () => $table->decimal('extra_class_amount', 12, 2)->default(0)->after('gross_salary'),
                'salary_calculation_payload' => fn () => $table->json('salary_calculation_payload')->nullable()->after('extra_class_amount'),
            ];

            foreach ($columns as $column => $callback) {
                if (! Schema::hasColumn('salary_payments', $column)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            foreach ([
                'total_scheduled_lectures',
                'attended_lectures',
                'missed_lectures',
                'late_joined_lectures',
                'total_scheduled_minutes',
                'total_payable_regular_minutes',
                'approved_extra_class_minutes',
                'deducted_minutes',
                'gross_salary',
                'extra_class_amount',
                'salary_calculation_payload',
            ] as $column) {
                if (Schema::hasColumn('salary_payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
