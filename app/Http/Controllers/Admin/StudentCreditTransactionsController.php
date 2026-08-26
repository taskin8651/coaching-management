<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCreditRequest;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentCreditTransaction;
use App\Models\StudentFeeLedger;
use App\Services\ReceiptNumberService;
use Gate;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StudentCreditTransactionsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('credit_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $transactions = StudentCreditTransaction::with(['student.user', 'ledger.feeStructure', 'feeInstallment', 'createdBy'])
            ->whereHas('student', fn ($q) => $this->scopeStudentQuery($q))
            ->latest()
            ->get();

        return view('admin.studentCredits.index', compact('transactions'));
    }

    /**
     * Applies (part of) a student's advance/credit balance against one of their installments,
     * without collecting fresh cash. Reuses the standard FeePayment pipeline (payment_mode =
     * credit_adjustment, no fee_account since no money physically moved) rather than inventing a
     * parallel accounting path, so recalculateFromPayments()/ledger recalculation stay the single
     * source of truth for "how much is this installment paid."
     */
    public function apply(ApplyCreditRequest $request, StudentFeeLedger $studentFeeLedger, ReceiptNumberService $receiptNumbers)
    {
        $this->checkAccess($studentFeeLedger);

        $data = $request->validated();

        $installment = FeeInstallment::where('student_fee_ledger_id', $studentFeeLedger->id)->find($data['fee_installment_id']);

        abort_if(! $installment, Response::HTTP_UNPROCESSABLE_ENTITY, 'Installment does not belong to this ledger.');

        $amount = (float) $data['amount'];

        abort_if(
            $amount > (float) $studentFeeLedger->advance_balance,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Amount exceeds available advance/credit balance (₹' . number_format($studentFeeLedger->advance_balance, 2) . ').'
        );

        abort_if(
            $amount > (float) $installment->due_amount,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Amount exceeds this installment\'s due amount (₹' . number_format($installment->due_amount, 2) . ').'
        );

        DB::transaction(function () use ($installment, $studentFeeLedger, $amount, $receiptNumbers) {
            $branchId = $installment->student->branch_id ?? null;
            $academicYear = optional($installment->feeStructure)->academic_year ?? $receiptNumbers->academicYearFor(now());
            $receipt = $receiptNumbers->next($branchId, $academicYear);

            $dueAfter = max((float) $installment->amount - (float) $installment->paid_amount - $amount, 0);

            $payment = FeePayment::create([
                'branch_id' => $branchId,
                'student_id' => $installment->student_id,
                'course_id' => $installment->student->course_id ?? null,
                'batch_id' => $installment->student->batch_id ?? null,
                'fee_structure_id' => $installment->fee_structure_id,
                'fee_installment_id' => $installment->id,
                'fee_account_id' => null,
                'collected_by_id' => auth()->id(),
                'receipt_no' => $receipt['receipt_no'],
                'receipt_academic_year' => $receipt['academic_year'],
                'receipt_sequence_no' => $receipt['sequence_no'],
                'total_fee' => $installment->amount,
                'discount' => 0,
                'payable_amount' => $installment->amount,
                'paid_amount' => $amount,
                'due_amount' => $dueAfter,
                'payment_mode' => 'credit_adjustment',
                'payment_date' => now()->format('Y-m-d'),
                'payment_status' => $dueAfter <= 0 ? 'paid' : 'partial',
                'remarks' => 'Applied from advance/credit balance',
            ]);

            StudentCreditTransaction::create([
                'student_fee_ledger_id' => $studentFeeLedger->id,
                'student_id' => $installment->student_id,
                'fee_payment_id' => $payment->id,
                'fee_installment_id' => $installment->id,
                'type' => 'debit',
                'source' => 'applied_to_installment',
                'amount' => $amount,
                'remarks' => 'Applied to installment: ' . $installment->title,
                'created_by_id' => auth()->id(),
            ]);

            $installment->recalculateFromPayments();
        });

        return back()->with('message', 'Credit applied to installment successfully.');
    }

    private function checkAccess(StudentFeeLedger $ledger): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            ! $this->scopeStudentQuery(Student::query())->where('id', $ledger->student_id)->exists(),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }
}
