<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Http\Requests\UpdateRefundRequest;
use App\Models\FeePayment;
use App\Models\FeePaymentAllocation;
use App\Models\Refund;
use App\Models\Student;
use App\Models\StudentCreditTransaction;
use App\Models\StudentFeeLedger;
use Gate;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RefundsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('refund_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $refunds = Refund::with(['student.user', 'feePayment', 'feeAccount', 'approvedBy'])
            ->whereHas('student', fn ($q) => $this->scopeStudentQuery($q))
            ->latest()
            ->get();

        return view('admin.refunds.index', compact('refunds'));
    }

    public function create()
    {
        abort_if(Gate::denies('refund_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.refunds.create', $this->formData());
    }

    public function store(StoreRefundRequest $request)
    {
        $data = $request->validated();

        $student = $this->scopeStudentQuery(Student::query())->find($data['student_id']);
        abort_if(! $student, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledger = StudentFeeLedger::where('student_id', $student->id)->where('status', 'active')->latest('id')->first();

        abort_if(
            ! $ledger,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This student has no active fee ledger. A refund needs an assigned fee structure to link against.'
        );

        if (! empty($data['fee_payment_id'])) {
            $payment = FeePayment::find($data['fee_payment_id']);

            abort_if(
                ! $payment || $payment->student_id != $student->id,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Invalid payment for this student.'
            );

            abort_if(
                (float) $data['amount'] > $payment->refundableAmount(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Refund amount exceeds what is refundable on this payment (₹' . number_format($payment->refundableAmount(), 2) . '). A cancelled payment cannot be refunded.'
            );

            $data['fee_installment_id'] = $data['fee_installment_id'] ?? $payment->fee_installment_id;

            if ($data['fee_installment_id']) {
                $installmentRefundable = $this->refundableAmountForInstallment($payment, (int) $data['fee_installment_id']);

                abort_if(
                    (float) $data['amount'] > $installmentRefundable + 0.01,
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'Refund amount exceeds what this payment actually put toward that installment (₹' . number_format($installmentRefundable, 2) . ').'
                );
            }
        } else {
            abort_if(
                (float) $data['amount'] > (float) $ledger->advance_balance,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Refund amount exceeds the student\'s advance/credit balance (₹' . number_format($ledger->advance_balance, 2) . ').'
            );
        }

        $data['student_fee_ledger_id'] = $ledger->id;
        $data['approval_status'] = 'pending';
        $data['status'] = 'pending';
        $data['created_by_id'] = auth()->id();

        Refund::create($data);

        return redirect()->route('admin.refunds.index')->with('message', 'Refund request submitted successfully.');
    }

    public function show(Refund $refund)
    {
        abort_if(Gate::denies('refund_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($refund);

        $refund->load(['student.user', 'ledger.feeStructure', 'feePayment', 'feeInstallment', 'feeAccount', 'approvedBy', 'completedBy', 'createdBy']);

        return view('admin.refunds.show', compact('refund'));
    }

    public function edit(Refund $refund)
    {
        abort_if(Gate::denies('refund_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($refund);

        $this->assertMutable($refund);

        return view('admin.refunds.edit', $this->formData() + compact('refund'));
    }

    public function update(UpdateRefundRequest $request, Refund $refund)
    {
        $this->checkAccess($refund);

        $this->assertMutable($refund);

        $data = $request->validated();
        $amount = (float) $data['amount'];

        if ($refund->fee_payment_id) {
            $payment = FeePayment::find($refund->fee_payment_id);

            abort_if(
                $amount > $payment->refundableAmount(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Refund amount exceeds what is refundable on this payment (₹' . number_format($payment->refundableAmount(), 2) . ').'
            );

            if ($refund->fee_installment_id) {
                $installmentRefundable = $this->refundableAmountForInstallment($payment, $refund->fee_installment_id);

                abort_if(
                    $amount > $installmentRefundable + 0.01,
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'Refund amount exceeds what this payment actually put toward that installment (₹' . number_format($installmentRefundable, 2) . ').'
                );
            }
        } else {
            abort_if(
                $amount > (float) $refund->ledger->advance_balance,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Refund amount exceeds the student\'s advance/credit balance (₹' . number_format($refund->ledger->advance_balance, 2) . ').'
            );
        }

        $refund->update($data);

        return redirect()->route('admin.refunds.index')->with('message', 'Refund updated successfully.');
    }

    public function destroy(Refund $refund)
    {
        abort_if(Gate::denies('refund_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($refund);

        abort_if(
            $refund->approval_status !== 'pending' || $refund->status !== 'pending',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Only a pending, not-yet-completed refund can be deleted.'
        );

        $refund->delete();

        return back()->with('message', 'Refund deleted successfully.');
    }

    public function approve(Refund $refund)
    {
        abort_if(Gate::denies('refund_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($refund);

        abort_if($refund->status === 'completed', Response::HTTP_UNPROCESSABLE_ENTITY, 'This refund is already completed.');

        $refund->update([
            'approval_status' => 'approved',
            'approved_by_id' => auth()->id(),
            'approval_date' => now()->format('Y-m-d'),
        ]);

        return back()->with('message', 'Refund approved successfully.');
    }

    public function reject(Refund $refund)
    {
        abort_if(Gate::denies('refund_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($refund);

        abort_if($refund->status === 'completed', Response::HTTP_UNPROCESSABLE_ENTITY, 'This refund is already completed.');

        $refund->update([
            'approval_status' => 'rejected',
            'approved_by_id' => auth()->id(),
            'approval_date' => now()->format('Y-m-d'),
        ]);

        return back()->with('message', 'Refund rejected.');
    }

    /**
     * Marks the money as actually paid back out. Only reachable once approved. Nets the refund
     * back out of the linked installment (recalculateFromPayments picks up completed refunds
     * automatically) or, for a pure advance-balance refund, records a debit credit-transaction —
     * either way the ledger ends up recalculated.
     */
    public function complete(Refund $refund)
    {
        abort_if(Gate::denies('refund_complete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($refund);

        abort_if($refund->approval_status !== 'approved', Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund must be approved before it can be completed.');
        abort_if($refund->status === 'completed', Response::HTTP_UNPROCESSABLE_ENTITY, 'This refund is already completed.');

        DB::transaction(function () use ($refund) {
            $refund->update([
                'status' => 'completed',
                'completed_by_id' => auth()->id(),
                'completed_at' => now(),
            ]);

            if ($refund->fee_installment_id) {
                $refund->feeInstallment->recalculateFromPayments();
            } else {
                StudentCreditTransaction::create([
                    'student_fee_ledger_id' => $refund->student_fee_ledger_id,
                    'student_id' => $refund->student_id,
                    'fee_payment_id' => $refund->fee_payment_id,
                    'type' => 'debit',
                    'source' => 'refund',
                    'amount' => $refund->amount,
                    'remarks' => 'Refund #' . $refund->id,
                    'created_by_id' => auth()->id(),
                ]);

                $refund->ledger->recalculate();
            }
        });

        return back()->with('message', 'Refund marked as completed.');
    }

    /**
     * A payment can be split (via FeePaymentAllocation) across several installments — the
     * payment-level refundableAmount() alone doesn't stop a refund tagged to installment A from
     * draining money that was actually allocated to installment B. This caps a refund to what
     * this specific payment actually put toward the specific installment, minus refunds already
     * completed against that same (payment, installment) pair.
     */
    private function refundableAmountForInstallment(FeePayment $payment, int $installmentId): float
    {
        $directAmount = $payment->fee_installment_id == $installmentId ? (float) $payment->paid_amount : 0;

        $allocatedAmount = (float) FeePaymentAllocation::where('fee_payment_id', $payment->id)
            ->where('fee_installment_id', $installmentId)
            ->sum('amount');

        $priorRefunded = (float) Refund::where('fee_payment_id', $payment->id)
            ->where('fee_installment_id', $installmentId)
            ->where('status', 'completed')
            ->sum('amount');

        return max($directAmount + $allocatedAmount - $priorRefunded, 0);
    }

    private function formData(): array
    {
        return [
            'students' => $this->scopeStudentQuery(Student::with('user'))
                ->get()
                ->mapWithKeys(fn ($s) => [$s->id => $s->user->name ?? $s->student_code ?? 'Student #' . $s->id])
                ->prepend(trans('global.pleaseSelect'), ''),
            'feeAccounts' => \App\Models\FeeAccount::where('status', 'active')->orderBy('name')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), ''),
            'paymentsByStudent' => $this->paymentsByStudent(),
        ];
    }

    /**
     * Each non-cancelled payment a student has, grouped by student_id, with its refundable
     * amount and whether it spans multiple installments (multi-allocated) — so the create form
     * can cascade Student -> Payment the same way fee payments cascade Student -> Installment,
     * and only reveal the installment select when the chosen payment actually needs one.
     */
    private function paymentsByStudent(): array
    {
        return FeePayment::with('allocations.feeInstallment')
            ->where('payment_status', '!=', 'cancelled')
            ->whereHas('student', fn ($q) => $this->scopeStudentQuery($q))
            ->get()
            ->filter(fn ($payment) => $payment->refundableAmount() > 0)
            ->groupBy('student_id')
            ->map(fn ($payments) => $payments->map(fn ($payment) => [
                'id' => $payment->id,
                'name' => $payment->receipt_no . ' — Refundable ₹' . number_format($payment->refundableAmount(), 0),
                'refundable' => $payment->refundableAmount(),
                'fee_installment_id' => $payment->fee_installment_id,
                'installments' => $payment->allocations->map(fn ($a) => [
                    'id' => $a->fee_installment_id,
                    'name' => $a->feeInstallment->title ?? ('Installment #' . $a->fee_installment_id),
                    'amount' => (float) $a->amount,
                ])->unique('id')->values(),
            ])->values())
            ->toArray();
    }

    private function assertMutable(Refund $refund): void
    {
        abort_if(
            $refund->approval_status === 'approved' || $refund->status === 'completed',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'An approved or completed refund cannot be edited.'
        );
    }

    private function checkAccess(Refund $refund): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            ! $this->scopeStudentQuery(Student::query())->where('id', $refund->student_id)->exists(),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }
}
