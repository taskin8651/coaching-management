<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\ReceiptNumberSequence;
use Illuminate\Support\Facades\DB;

class ReceiptNumberService
{
    /**
     * Issues the next unique receipt number for a branch + academic year, e.g. "RJT/2026-27/000001".
     * Locks the sequence row for the duration of the transaction so concurrent payment entry can
     * never hand out the same number twice (the previous FeePayment::latest('id')->first()->id + 1
     * approach had exactly that race condition).
     */
    public function next(?int $branchId, string $academicYear): array
    {
        return DB::transaction(function () use ($branchId, $academicYear) {
            $sequence = ReceiptNumberSequence::where('branch_id', $branchId)
                ->where('academic_year', $academicYear)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = ReceiptNumberSequence::create([
                    'branch_id' => $branchId,
                    'academic_year' => $academicYear,
                    'last_number' => 0,
                ]);

                $sequence = ReceiptNumberSequence::where('id', $sequence->id)->lockForUpdate()->first();
            }

            $sequence->increment('last_number');

            $branchCode = $branchId ? (Branch::find($branchId)->branch_code ?? 'HQ') : 'HQ';

            $receiptNo = sprintf('%s/%s/%06d', $branchCode, $academicYear, $sequence->last_number);

            return [
                'receipt_no' => $receiptNo,
                'academic_year' => $academicYear,
                'sequence_no' => $sequence->last_number,
            ];
        });
    }

    /**
     * Indian academic-year cycle (Apr–Mar), e.g. a payment dated 2026-08-24 resolves to "2026-27",
     * one dated 2027-02-10 also resolves to "2026-27".
     */
    public function academicYearFor(\DateTimeInterface $date): string
    {
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        $startYear = $month >= 4 ? $year : $year - 1;

        return $startYear . '-' . substr((string) ($startYear + 1), -2);
    }
}
