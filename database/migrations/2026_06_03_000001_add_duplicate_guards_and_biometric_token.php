<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('whatsapp_settings', 'biometric_device_token')) {
                $table->string('biometric_device_token')->nullable()->after('sender_number');
            }
        });

        $this->deduplicateStudentBatches();
        $this->deduplicateStudentAttendances();
        $this->deduplicateHomeworkSubmissions();

        Schema::table('student_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('student_batches', 'unique_key')) {
                $table->string('unique_key')->nullable()->after('status');
            }
        });
        $this->populateStudentBatchKeys();
        Schema::table('student_batches', function (Blueprint $table) {
            $table->unique('unique_key', 'student_batches_unique_key');
        });

        Schema::table('student_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('student_attendances', 'unique_key')) {
                $table->string('unique_key')->nullable()->after('remarks');
            }
        });
        $this->populateStudentAttendanceKeys();
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->unique('unique_key', 'student_attendances_unique_key');
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('homework_submissions', 'unique_key')) {
                $table->string('unique_key')->nullable()->after('remarks');
            }
        });
        $this->populateHomeworkSubmissionKeys();
        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->unique('unique_key', 'homework_submissions_unique_key');
        });
    }

    public function down(): void
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('homework_submissions', 'unique_key')) {
                $table->dropUnique('homework_submissions_unique_key');
                $table->dropColumn('unique_key');
            }
        });

        Schema::table('student_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('student_attendances', 'unique_key')) {
                $table->dropUnique('student_attendances_unique_key');
                $table->dropColumn('unique_key');
            }
        });

        Schema::table('student_batches', function (Blueprint $table) {
            if (Schema::hasColumn('student_batches', 'unique_key')) {
                $table->dropUnique('student_batches_unique_key');
                $table->dropColumn('unique_key');
            }
        });

        Schema::table('whatsapp_settings', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_settings', 'biometric_device_token')) {
                $table->dropColumn('biometric_device_token');
            }
        });
    }

    private function deduplicateStudentBatches(): void
    {
        $duplicates = DB::table('student_batches')
            ->selectRaw('MIN(id) as keep_id, GROUP_CONCAT(id) as ids')
            ->whereNull('deleted_at')
            ->groupBy('student_id', 'batch_id', 'subject_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = collect(explode(',', $duplicate->ids))->map(fn ($id) => (int) $id)->reject(fn ($id) => $id === (int) $duplicate->keep_id);
            DB::table('student_batches')->whereIn('id', $ids)->update(['deleted_at' => now(), 'updated_at' => now()]);
        }
    }

    private function deduplicateStudentAttendances(): void
    {
        $duplicates = DB::table('student_attendances')
            ->selectRaw('MIN(id) as keep_id, GROUP_CONCAT(id) as ids')
            ->whereNull('deleted_at')
            ->groupBy('student_id', 'batch_id', 'subject_id', 'attendance_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = collect(explode(',', $duplicate->ids))->map(fn ($id) => (int) $id)->reject(fn ($id) => $id === (int) $duplicate->keep_id);
            DB::table('student_attendances')->whereIn('id', $ids)->update(['deleted_at' => now(), 'updated_at' => now()]);
        }
    }

    private function deduplicateHomeworkSubmissions(): void
    {
        $duplicates = DB::table('homework_submissions')
            ->selectRaw('MIN(id) as keep_id, GROUP_CONCAT(id) as ids')
            ->whereNull('deleted_at')
            ->groupBy('homework_id', 'student_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = collect(explode(',', $duplicate->ids))->map(fn ($id) => (int) $id)->reject(fn ($id) => $id === (int) $duplicate->keep_id);
            DB::table('homework_submissions')->whereIn('id', $ids)->update(['deleted_at' => now(), 'updated_at' => now()]);
        }
    }

    private function populateStudentBatchKeys(): void
    {
        DB::table('student_batches')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $key = $row->deleted_at
                    ? 'deleted:' . $row->id
                    : implode(':', ['active', $row->student_id, $row->batch_id, $row->subject_id ?: 0]);

                DB::table('student_batches')->where('id', $row->id)->update(['unique_key' => $key]);
            }
        });
    }

    private function populateStudentAttendanceKeys(): void
    {
        DB::table('student_attendances')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $key = $row->deleted_at
                    ? 'deleted:' . $row->id
                    : implode(':', ['active', $row->student_id, $row->batch_id, $row->subject_id ?: 0, $row->attendance_date]);

                DB::table('student_attendances')->where('id', $row->id)->update(['unique_key' => $key]);
            }
        });
    }

    private function populateHomeworkSubmissionKeys(): void
    {
        DB::table('homework_submissions')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $key = $row->deleted_at
                    ? 'deleted:' . $row->id
                    : implode(':', ['active', $row->homework_id, $row->student_id]);

                DB::table('homework_submissions')->where('id', $row->id)->update(['unique_key' => $key]);
            }
        });
    }
};
