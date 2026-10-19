<?php

namespace App\Services;

use App\Models\FeeInstallment;
use App\Models\FeeInstallmentItem;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFeeLedger;

class StudentFeeLedgerAssignmentService
{
    public function ensureLedger(FeeStructure $feeStructure, Student $student, ?int $assignedById = null): StudentFeeLedger
    {
        $ledger = StudentFeeLedger::firstOrCreate(
            ['student_id' => $student->id, 'fee_structure_id' => $feeStructure->id],
            [
                'fee_structure_version' => $feeStructure->version_no,
                'net_payable' => $feeStructure->total_fee,
                'assigned_by_id' => $assignedById,
                'assigned_at' => now(),
                'status' => 'active',
            ]
        );

        if (! $ledger->installments()->exists()) {
            $ledger->loadMissing('feeStructure.items', 'feeStructure.installmentTemplates');

            // Attach older installment rows so existing payments remain part of this ledger.
            $legacyInstallments = FeeInstallment::where('student_id', $student->id)
                ->where('fee_structure_id', $feeStructure->id)
                ->whereNull('student_fee_ledger_id')
                ->get();

            if ($legacyInstallments->isNotEmpty()) {
                foreach ($legacyInstallments as $installment) {
                    $installment->update(['student_fee_ledger_id' => $ledger->id]);
                }
            } else {
                $this->generateInstallments($feeStructure, $ledger, $student);
            }
        }

        $ledger->refresh()->recalculate();

        return $ledger->fresh();
    }

    private function generateInstallments(FeeStructure $feeStructure, StudentFeeLedger $ledger, Student $student): void
    {
        $templates = $feeStructure->installmentTemplates;
        $items = $feeStructure->items;
        $structureTotal = (float) $feeStructure->total_fee;

        if ($templates->isEmpty()) {
            $installment = FeeInstallment::create([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'student_fee_ledger_id' => $ledger->id,
                'title' => $feeStructure->title ?? 'Fee',
                'amount' => $structureTotal,
                'paid_amount' => 0,
                'due_amount' => $structureTotal,
                'status' => 'pending',
            ]);

            $this->createInstallmentItems($installment, $items, $structureTotal, $structureTotal);

            return;
        }

        $resolvedAmounts = $templates->map(fn ($template) => $template->resolvedAmount($structureTotal));
        $allocatedSoFar = 0;

        foreach ($templates as $index => $template) {
            $amount = $index === $templates->count() - 1
                ? round($structureTotal - $allocatedSoFar, 2)
                : round($resolvedAmounts[$index], 2);
            $allocatedSoFar += $amount;

            $installment = FeeInstallment::create([
                'student_id' => $student->id,
                'fee_structure_id' => $feeStructure->id,
                'student_fee_ledger_id' => $ledger->id,
                'fee_structure_installment_id' => $template->id,
                'fee_account_id' => $template->fee_account_id,
                'title' => $template->title,
                'amount' => max($amount, 0),
                'paid_amount' => 0,
                'due_amount' => max($amount, 0),
                'due_date' => $template->due_date,
                'status' => 'pending',
                'late_fee_enabled' => $template->late_fee_enabled,
                'late_fee_type' => $template->late_fee_type,
                'late_fee_amount' => $template->late_fee_amount,
                'late_fee_percentage' => $template->late_fee_percentage,
                'late_fee_grace_days' => $template->late_fee_grace_days,
                'late_fee_max_amount' => $template->late_fee_max_amount,
            ]);

            $this->createInstallmentItems($installment, $items, $amount, $structureTotal);
        }
    }

    private function createInstallmentItems(FeeInstallment $installment, $items, float $installmentAmount, float $structureTotal): void
    {
        if ($structureTotal <= 0) {
            return;
        }

        $ratio = $installmentAmount / $structureTotal;

        foreach ($items as $item) {
            FeeInstallmentItem::create([
                'fee_installment_id' => $installment->id,
                'fee_head_id' => $item->fee_head_id,
                'amount' => round(((float) $item->amount) * $ratio, 2),
                'gst_percent' => $item->gst_percent,
                'gst_amount' => round(((float) $item->gst_amount) * $ratio, 2),
                'line_total' => round(((float) $item->line_total) * $ratio, 2),
            ]);
        }
    }
}
