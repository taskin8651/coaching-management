<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'permissions',
        'roles',
        'users',
        'branches',
        'courses',
        'subjects',
        'batches',
        'teachers',
        'staff',
        'students',
        'enquiries',
        'enquiry_follow_ups',
        'fee_payments',
        'expenses',
        'salary_payments',
        'exams',
        'exam_results',
        'study_materials',
        'notices',
        'teacher_assignments',
        'admissions',
        'fee_structures',
        'student_batches',
        'student_attendances',
        'staff_attendances',
        'faculty_log_books',
        'extra_classes',
        'transport_routes',
        'homeworks',
        'homework_submissions',
        'student_remarks',
        'maintenance_requests',
        'inventory_items',
        'inventory_transactions',
        'fee_installments',
        'report_cards',
    ];

    private array $softDeleteUniqueIndexes = [
        'student_batches' => ['student_batch_unique'],
        'student_attendances' => ['student_attendance_unique'],
        'homework_submissions' => ['homework_student_unique'],
    ];

    public function up(): void
    {
        $this->ensureForeignKeyIndexes();

        foreach ($this->softDeleteUniqueIndexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
                foreach ($indexes as $index) {
                    if ($this->indexExists($table, $index)) {
                        $blueprint->dropUnique($index);
                    }
                }
            });
        }

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('deleted_at');
            });
        }
    }

    public function down(): void
    {
        // Soft deletes are intentionally removed project-wide.
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            return ! empty(DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$index]));
        }

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('$table')"))->contains(fn ($row) => $row->name === $index);
        }

        return true;
    }

    private function ensureForeignKeyIndexes(): void
    {
        $indexes = [
            ['student_batches', ['student_id'], 'student_batches_student_id_plain_index'],
            ['student_batches', ['batch_id'], 'student_batches_batch_id_plain_index'],
            ['student_batches', ['subject_id'], 'student_batches_subject_id_plain_index'],
            ['student_attendances', ['student_id'], 'student_attendances_student_id_plain_index'],
            ['student_attendances', ['batch_id'], 'student_attendances_batch_id_plain_index'],
            ['student_attendances', ['subject_id'], 'student_attendances_subject_id_plain_index'],
            ['student_attendances', ['biometric_device_log_id'], 'student_attendances_biometric_log_plain_index'],
            ['homework_submissions', ['homework_id'], 'homework_submissions_homework_id_plain_index'],
            ['homework_submissions', ['student_id'], 'homework_submissions_student_id_plain_index'],
        ];

        foreach ($indexes as [$table, $columns, $name]) {
            if (! Schema::hasTable($table) || $this->indexExists($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                $blueprint->index($columns, $name);
            });
        }
    }
};
