<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequest;
use App\Http\Requests\UpdateRefundRequest;
use App\Models\FeePayment;
use App\Models\FeePaymentAllocation;
use App\Models\FeeInstallment;
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

        DB::transaction(function () use ($data) {
            $student = $this->scopeStudentQuery(Student::query())->lockForUpdate()->find($data['student_id']);
            abort_if(! $student, Response::HTTP_FORBIDDEN, '403 Forbidden');

            $payment = null;
            if (! empty($data['fee_payment_id'])) {
                $payment = FeePayment::whereKey($data['fee_payment_id'])->lockForUpdate()->first();
                abort_if(! $payment || (int) $payment->student_id !== (int) $student->id, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid payment for this student.');
                abort_if($payment->payment_status === 'cancelled', Response::HTTP_UNPROCESSABLE_ENTITY, 'A cancelled payment cannot be refunded.');

                $ledger = $this->ledgerForPayment($student, $payment);
            } else {
                abort_if(! empty($data['fee_installment_id']), Response::HTTP_UNPROCESSABLE_ENTITY, 'Select a fee receipt before selecting an installment.');
                $ledger = StudentFeeLedger::where('student_id', $student->id)
                    ->where('status', 'active')->latest('id')->lockForUpdate()->first();
            }

            abort_if(! $ledger, Response::HTTP_UNPROCESSABLE_ENTITY, 'This student has no active fee ledger. A refund needs an assigned fee structure to link against.');

            $amount = round((float) $data['amount'], 2);
            if ($payment) {
                // Receipt refunds go back through the same account that received the payment.
                if ($payment->fee_account_id) {
                    $data['fee_account_id'] = $payment->fee_account_id;
                }

                $available = $payment->refundableAmount();
                abort_if($amount > $available, Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund amount exceeds what is refundable on this payment (Rs. ' . number_format($available, 2) . ').');

                $installmentId = $this->resolvePaymentInstallment($payment, isset($data['fee_installment_id']) ? (int) $data['fee_installment_id'] : null);
                if ($installmentId) {
                    $installmentAvailable = $this->refundableAmountForInstallment($payment, $installmentId);
                    abort_if($amount > $installmentAvailable, Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund amount exceeds what this payment put toward the selected installment (Rs. ' . number_format($installmentAvailable, 2) . ').');
                }
                $data['fee_installment_id'] = $installmentId;
            } else {
                $available = max((float) $ledger->advance_balance - $this->reservedAdvanceRefunds($ledger), 0);
                abort_if($amount > $available, Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund amount exceeds the available advance/credit balance (Rs. ' . number_format($available, 2) . ').');
                $data['fee_installment_id'] = null;
            }

            $data['student_fee_ledger_id'] = $ledger->id;
            $data['approval_status'] = 'pending';
            $data['status'] = 'pending';
            $data['created_by_id'] = auth()->id();

            Refund::create($data);
        });

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
        $data = $request->validated();

        DB::transaction(function () use ($refund, $data) {
            $lockedRefund = Refund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            $this->checkAccess($lockedRefund);
            $this->assertMutable($lockedRefund);
            $amount = round((float) $data['amount'], 2);

            if ($lockedRefund->fee_payment_id) {
                $payment = FeePayment::whereKey($lockedRefund->fee_payment_id)->lockForUpdate()->first();
                abort_if(! $payment || (int) $payment->student_id !== (int) $lockedRefund->student_id, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid payment for this refund.');
                abort_if($payment->payment_status === 'cancelled', Response::HTTP_UNPROCESSABLE_ENTITY, 'A cancelled payment cannot be refunded.');

                $available = $payment->refundableAmount($lockedRefund->id);
                abort_if($amount > $available, Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund amount exceeds what is refundable on this payment (Rs. ' . number_format($available, 2) . ').');

                if ($lockedRefund->fee_installment_id) {
                    $installmentAvailable = $this->refundableAmountForInstallment($payment, $lockedRefund->fee_installment_id, $lockedRefund->id);
                    abort_if($amount > $installmentAvailable, Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund amount exceeds what this payment put toward the installment (Rs. ' . number_format($installmentAvailable, 2) . ').');
                }
            } else {
                $ledger = StudentFeeLedger::whereKey($lockedRefund->student_fee_ledger_id)->lockForUpdate()->first();
                abort_if(! $ledger || $ledger->student_id != $lockedRefund->student_id, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid student ledger for this refund.');

                $available = max((float) $ledger->advance_balance - $this->reservedAdvanceRefunds($ledger, $lockedRefund->id), 0);
                abort_if($amount > $available, Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund amount exceeds the available advance/credit balance (Rs. ' . number_format($available, 2) . ').');
            }

            if ($lockedRefund->approval_status === 'rejected') {
                $data['approval_status'] = 'pending';
                $data['approved_by_id'] = null;
                $data['approval_date'] = null;
                $data['status'] = 'pending';
            }

            $lockedRefund->update($data);
        });

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
     * automatically) or, for a pure advance-balance refund, records a debit credit transaction.
     * either way the ledger ends up recalculated.
     */
    public function complete(Refund $refund)
    {
        abort_if(Gate::denies('refund_complete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->checkAccess($refund);

        DB::transaction(function () use ($refund) {
            $lockedRefund = Refund::whereKey($refund->id)->lockForUpdate()->firstOrFail();
            $this->checkAccess($lockedRefund);

            abort_if($lockedRefund->approval_status !== 'approved', Response::HTTP_UNPROCESSABLE_ENTITY, 'Refund must be approved before it can be completed.');
            abort_if($lockedRefund->status !== 'pending', Response::HTTP_UNPROCESSABLE_ENTITY, 'This refund is already completed or cancelled.');

            $lockedRefund->update([
                'status' => 'completed',
                'completed_by_id' => auth()->id(),
                'completed_at' => now(),
            ]);

            if ($lockedRefund->fee_installment_id) {
                $installment = $lockedRefund->feeInstallment()->lockForUpdate()->firstOrFail();
                $installment->recalculateFromPayments();
            } elseif (! $lockedRefund->fee_payment_id) {
                StudentCreditTransaction::create([
                    'student_fee_ledger_id' => $lockedRefund->student_fee_ledger_id,
                    'student_id' => $lockedRefund->student_id,
                    'fee_payment_id' => null,
                    'type' => 'debit',
                    'source' => 'refund',
                    'amount' => $lockedRefund->amount,
                    'remarks' => 'Refund #' . $lockedRefund->id,
                    'created_by_id' => auth()->id(),
                ]);

                StudentFeeLedger::whereKey($lockedRefund->student_fee_ledger_id)->lockForUpdate()->firstOrFail()->recalculate();
            }
        });

        return back()->with('message', 'Refund marked as completed.');
    }
    /**
     * A payment can be split (via FeePaymentAllocation) across several installments; the
     * payment-level refundableAmount() alone doesn't stop a refund tagged to installment A from
     * draining money that was actually allocated to installment B. This caps a refund to what
     * this specific payment actually put toward the specific installment, minus refunds already
     * completed against that same (payment, installment) pair.
     */
    private function refundableAmountForInstallment(FeePayment $payment, int $installmentId, ?int $ignoreRefundId = null): float
    {
        $directAmount = $payment->fee_installment_id == $installmentId ? (float) $payment->paid_amount : 0;

        // Allocations are only written for payments without a direct installment link.
        // Ignore inconsistent legacy allocations when the payment is directly linked.
        $allocatedAmount = $payment->fee_installment_id
            ? 0
            : (float) FeePaymentAllocation::where('fee_payment_id', $payment->id)
                ->where('fee_installment_id', $installmentId)
                ->sum('amount');

        $reservedRefunds = (float) Refund::where('fee_payment_id', $payment->id)
            ->where('fee_installment_id', $installmentId)
            ->where('approval_status', '!=', 'rejected')
            ->where('status', '!=', 'cancelled')
            ->when($ignoreRefundId, fn ($query) => $query->where('id', '!=', $ignoreRefundId))
            ->sum('amount');

        $creditGenerated = (float) $payment->creditTransactions()
            ->where('type', 'credit')->where('source', 'overpayment')->sum('amount');
        $appliedToInstallment = min($directAmount + $allocatedAmount, max((float) $payment->paid_amount - $creditGenerated, 0));

        return max($appliedToInstallment - $reservedRefunds, 0);
    }

    private function resolvePaymentInstallment(FeePayment $payment, ?int $requestedInstallmentId): ?int
    {
        if ($payment->fee_installment_id) {
            abort_if(
                $requestedInstallmentId && $requestedInstallmentId !== (int) $payment->fee_installment_id,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'The selected installment does not belong to this receipt.'
            );

            return (int) $payment->fee_installment_id;
        }

        $installmentIds = FeePaymentAllocation::where('fee_payment_id', $payment->id)
            ->select('fee_installment_id')->distinct()->pluck('fee_installment_id')->map(fn ($id) => (int) $id);

        if ($requestedInstallmentId) {
            abort_if(
                ! $installmentIds->contains($requestedInstallmentId),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'The selected installment is not allocated to this receipt.'
            );

            return $requestedInstallmentId;
        }

        abort_if($installmentIds->count() > 1, Response::HTTP_UNPROCESSABLE_ENTITY, 'Select the installment this refund applies to.');

        return $installmentIds->first();
    }

    private function ledgerForPayment(Student $student, FeePayment $payment): ?StudentFeeLedger
    {
        $installmentLedgerId = $payment->fee_installment_id
            ? FeeInstallment::whereKey($payment->fee_installment_id)->value('student_fee_ledger_id')
            : null;

        if (! $installmentLedgerId) {
            $installmentLedgerId = FeePaymentAllocation::where('fee_payment_id', $payment->id)
                ->join('fee_installments', 'fee_installments.id', '=', 'fee_payment_allocations.fee_installment_id')
                ->whereNotNull('fee_installments.student_fee_ledger_id')
                ->value('fee_installments.student_fee_ledger_id');
        }

        if ($installmentLedgerId) {
            return StudentFeeLedger::whereKey($installmentLedgerId)
                ->where('student_id', $student->id)->where('status', 'active')->lockForUpdate()->first();
        }

        $query = StudentFeeLedger::where('student_id', $student->id)->where('status', 'active');
        if ($payment->fee_structure_id) {
            $matching = (clone $query)->where('fee_structure_id', $payment->fee_structure_id)->lockForUpdate()->first();
            if ($matching) {
                return $matching;
            }
        }

        return $query->latest('id')->lockForUpdate()->first();
    }

    private function reservedAdvanceRefunds(StudentFeeLedger $ledger, ?int $ignoreRefundId = null): float
    {
        return (float) Refund::where('student_fee_ledger_id', $ledger->id)
            ->whereNull('fee_payment_id')
            ->where('approval_status', '!=', 'rejected')
            ->where('status', 'pending')
            ->when($ignoreRefundId, fn ($query) => $query->where('id', '!=', $ignoreRefundId))
            ->sum('amount');
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
            'advanceBalances' => $this->advanceBalances(),
        ];
    }

    private function advanceBalances(): array
    {
        return StudentFeeLedger::where('status', 'active')
            ->whereHas('student', fn ($query) => $this->scopeStudentQuery($query))
            ->orderByDesc('id')
            ->get(['id', 'student_id', 'advance_balance'])
            ->unique('student_id')
            ->mapWithKeys(fn ($ledger) => [
                $ledger->student_id => max((float) $ledger->advance_balance - $this->reservedAdvanceRefunds($ledger), 0),
            ])
            ->all();
    }

    /**
     * Each non-cancelled payment a student has, grouped by student_id, with its refundable
     * amount and whether it spans multiple installments, so the create form
     * can cascade Student -> Payment the same way fee payments cascade Student -> Installment,
     * and only reveal the installment select when the chosen payment actually needs one.
     */
    private function paymentsByStudent(): array
    {
        return FeePayment::with(['feeAccount', 'feeInstallment', 'allocations.feeInstallment'])
            ->where('payment_status', '!=', 'cancelled')
            ->whereHas('student', fn ($q) => $this->scopeStudentQuery($q))
            ->get()
            ->filter(fn ($payment) => $payment->refundableAmount() > 0)
            ->groupBy('student_id')
            ->map(fn ($payments) => $payments->map(function ($payment) {
                $installments = collect([$payment->feeInstallment])
                    ->merge($payment->allocations->pluck('feeInstallment'))
                    ->filter()
                    ->unique('id')
                    ->map(fn ($installment) => [
                        'id' => $installment->id,
                        'name' => ($installment->title ?? ('Installment #' . $installment->id))
                            . ' - Refundable Rs. ' . number_format($this->refundableAmountForInstallment($payment, (int) $installment->id), 2),
                        'refundable' => $this->refundableAmountForInstallment($payment, (int) $installment->id),
                    ])
                    ->filter(fn ($installment) => $installment['refundable'] > 0)
                    ->values();

                $refundable = $payment->refundableAmount();

                return [
                    'id' => $payment->id,
                    'name' => $payment->receipt_no . ' - Refundable Rs. ' . number_format($refundable, 2),
                    'refundable' => $refundable,
                    'fee_account_id' => $payment->fee_account_id,
                    'fee_account_name' => $payment->feeAccount->name ?? null,
                    'fee_installment_id' => $payment->fee_installment_id,
                    'installments' => $installments,
                ];
            })->values())
            ->toArray();
    }
    private function assertMutable(Refund $refund): void
    {
        abort_if(
            $refund->approval_status === 'approved' || in_array($refund->status, ['completed', 'cancelled'], true),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'An approved, completed, or cancelled refund cannot be edited.'
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
