<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('batch_subject')) {
            return;
        }

        $now = now();
        $pairs = collect();

        if (Schema::hasTable('timetables')) {
            $pairs = $pairs->merge(
                DB::table('timetables')
                    ->whereNotNull('batch_id')
                    ->whereNotNull('subject_id')
                    ->select('batch_id', 'subject_id')
                    ->distinct()
                    ->get()
            );
        }

        if (Schema::hasTable('student_batches')) {
            $pairs = $pairs->merge(
                DB::table('student_batches')
                    ->whereNotNull('batch_id')
                    ->whereNotNull('subject_id')
                    ->select('batch_id', 'subject_id')
                    ->distinct()
                    ->get()
            );
        }

        if (Schema::hasTable('teacher_assignments')) {
            $pairs = $pairs->merge(
                DB::table('teacher_assignments')
                    ->whereNotNull('batch_id')
                    ->whereNotNull('subject_id')
                    ->select('batch_id', 'subject_id')
                    ->distinct()
                    ->get()
            );
        }

        $rows = $pairs
            ->unique(fn ($row) => $row->batch_id . ':' . $row->subject_id)
            ->map(fn ($row) => [
                'batch_id' => $row->batch_id,
                'subject_id' => $row->subject_id,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('batch_subject')->insertOrIgnore($chunk);
        }
    }

    public function down(): void
    {
        // No destructive rollback: these links may be edited by users after backfill.
    }
};
