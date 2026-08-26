<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('fee_structures', 'admission_fee')) {
            return;
        }

        $feeHeadIds = DB::table('fee_heads')->pluck('id', 'code');

        $legacyColumns = [
            'admission_fee' => 'admission',
            'tuition_fee' => 'tuition',
            'exam_fee' => 'exam',
            'material_fee' => 'material',
            'other_fee' => 'other',
        ];

        $now = now();

        DB::table('fee_structures')->orderBy('id')->chunk(200, function ($structures) use ($legacyColumns, $feeHeadIds, $now) {
            $rows = [];

            foreach ($structures as $structure) {
                $sortOrder = 0;

                foreach ($legacyColumns as $column => $code) {
                    $amount = (float) ($structure->{$column} ?? 0);

                    if ($amount <= 0 || ! isset($feeHeadIds[$code])) {
                        continue;
                    }

                    $rows[] = [
                        'fee_structure_id' => $structure->id,
                        'fee_head_id' => $feeHeadIds[$code],
                        'amount' => $amount,
                        'gst_applicable' => false,
                        'gst_percent' => 0,
                        'gst_amount' => 0,
                        'line_total' => $amount,
                        'sort_order' => $sortOrder++,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($rows) {
                DB::table('fee_structure_items')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        // Data-only migration; nothing to reverse.
    }
};
