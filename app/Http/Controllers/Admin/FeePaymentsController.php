<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelFeePaymentRequest;
use App\Http\Requests\StoreFeePaymentRequest;
use App\Http\Requests\UpdateFeePaymentRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\FeeAccount;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use App\Models\FeePaymentAllocation;
use App\Models\FeeStructure;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentCreditTransaction;
use App\Models\StudentFeeLedger;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\ReceiptNumberService;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FeePaymentsController extends Controller
{
    use AppliesErpScope;
    public function index()
    {
        abort_if(Gate::denies('fee_payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feePayments = FeePayment::with([
            'branch',
            'student.user',
            'course',
            'batch',
            'feeStructure',
            'feeAccount',
            'collectedBy',
            'eventEnrollment.event',
            'eventEnrollment.student.user',
            'eventEnrollment.externalContact',
        ]);

        if (auth()->user()->is_admin) {
            // Admin all
        } elseif ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            $student
                ? $feePayments->where('student_id', $student->id)
                : $feePayments->whereRaw('1 = 0');

        } elseif ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            $batchIds->count()
                ? $feePayments->whereIn('batch_id', $batchIds)
                : $feePayments->whereRaw('1 = 0');

        } else {
            $branchId = $this->getUserBranchId();

            $branchId
                ? $feePayments->where('branch_id', $branchId)
                : $feePayments->whereRaw('1 = 0');
        }

        $feePayments = $feePayments->latest()->get();

        return view('admin.feePayments.index', compact('feePayments'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.feePayments.create', $this->formData());
    }

    public function store(StoreFeePaymentRequest $request, WhatsappService $whatsapp, ReceiptNumberService $receiptNumbers)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $allocateMultiple = (bool) ($data['allocate_multiple'] ?? false);

        if ($allocateMultiple) {
            abort_if(Gate::denies('fee_payment_allocate'), Response::HTTP_FORBIDDEN, '403 Forbidden');

            $data['fee_installment_id'] = null;
        }

        $data = $this->prepareStudentAndStructureData($data);

        $this->validatePaymentDataAccess($data);

        $data = $this->preparePaymentData($data, null, $receiptNumbers);

        $feePayment = DB::transaction(function () use ($data, $allocateMultiple) {
            $feePayment = FeePayment::create($data);

            if ($allocateMultiple) {
                $this->applyAllocations($feePayment, $data['allocations'] ?? []);
            } elseif ($feePayment->fee_installment_id) {
                $installment = FeeInstallment::find($feePayment->fee_installment_id);

                if ($installment) {
                    $this->recalculateAndCreditExcess($feePayment, $installment);
                }
            }

            return $feePayment;
        });

        if ($feePayment->student) {
            $whatsapp->sendStudentGuardianMessage(
                $feePayment->student,
                'fee_payment',
                'Fee received. Receipt No: ' . $feePayment->receipt_no . ', Paid: ' . $feePayment->paid_amount . ', Due: ' . $feePayment->due_amount
            );
        }

        return redirect()->route('admin.fee-payments.index')->with('message', 'Fee payment created successfully.');
    }

    /**
     * Splits a payment across multiple installments: one FeePaymentAllocation row per entry
     * (fee_payments.fee_installment_id stays null for these), recalculates every distinct
     * installment touched, then any amount left over (paid_amount - sum(allocations)) becomes
     * advance/credit — same "unallocated remainder = credit" rule as the single-installment path.
     */
    private function applyAllocations(FeePayment $feePayment, array $allocations): void
    {
        $allocatedTotal = 0;

        abort_if(
            collect($allocations)->sum('amount') > (float) $feePayment->paid_amount + 0.01,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Allocated amounts exceed the paid amount.'
        );

        $touchedInstallmentIds = [];

        foreach ($allocations as $allocation) {
            $installment = FeeInstallment::find($allocation['fee_installment_id']);

            abort_if(
                ! $installment || $installment->student_id != $feePayment->student_id,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Invalid installment in allocation.'
            );

            FeePaymentAllocation::create([
                'fee_payment_id' => $feePayment->id,
                'fee_installment_id' => $installment->id,
                'amount' => $allocation['amount'],
            ]);

            $allocatedTotal += (float) $allocation['amount'];
            $touchedInstallmentIds[$installment->id] = true;
        }

        foreach (array_keys($touchedInstallmentIds) as $installmentId) {
            FeeInstallment::find($installmentId)?->recalculateFromPayments();
        }

        $excess = round((float) $feePayment->paid_amount - $allocatedTotal, 2);

        if ($excess > 0) {
            $ledger = StudentFeeLedger::where('student_id', $feePayment->student_id)->where('status', 'active')->latest('id')->first();

            if ($ledger) {
                StudentCreditTransaction::create([
                    'student_fee_ledger_id' => $ledger->id,
                    'student_id' => $feePayment->student_id,
                    'fee_payment_id' => $feePayment->id,
                    'type' => 'credit',
                    'source' => 'overpayment',
                    'amount' => $excess,
                    'remarks' => 'Unallocated remainder from receipt ' . $feePayment->receipt_no,
                    'created_by_id' => auth()->id(),
                ]);

                $ledger->recalculate();
            }
        }
    }

    /**
     * Reads the installment's due amount BEFORE recalculating (that's the most this payment can
     * actually settle against it), recalculates as normal, then whatever this payment paid beyond
     * that prior-due figure is an overpayment — recorded as advance/credit rather than silently
     * lost (recalculateFromPayments() caps paid_amount at the installment's own amount).
     */
    private function recalculateAndCreditExcess(FeePayment $feePayment, FeeInstallment $installment): void
    {
        $priorDue = (float) $installment->due_amount;

        $installment->recalculateFromPayments();

        $excess = round((float) $feePayment->paid_amount - $priorDue, 2);

        if ($excess > 0) {
            $ledger = $installment->ledger;

            if ($ledger) {
                StudentCreditTransaction::create([
                    'student_fee_ledger_id' => $ledger->id,
                    'student_id' => $feePayment->student_id,
                    'fee_payment_id' => $feePayment->id,
                    'fee_installment_id' => $installment->id,
                    'type' => 'credit',
                    'source' => 'overpayment',
                    'amount' => $excess,
                    'remarks' => 'Overpayment on receipt ' . $feePayment->receipt_no,
                    'created_by_id' => auth()->id(),
                ]);

                $ledger->recalculate();
            }
        }
    }

    public function show(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

        $feePayment->load([
            'branch',
            'student.user',
            'course',
            'batch',
            'feeStructure',
            'feeAccount',
            'concession',
            'collectedBy',
            'cancelledBy',
            'allocations.feeInstallment',
            'refunds',
            'eventEnrollment.event',
            'eventEnrollment.student.user',
            'eventEnrollment.externalContact',
        ]);

        return view('admin.feePayments.show', compact('feePayment'));
    }

    public function edit(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

        $formData = $this->formData();

        // The installment this payment is already linked to may have just become fully
        // paid because of this very payment — still surface it so the edit form doesn't
        // silently lose the current selection.
        if ($feePayment->fee_installment_id && $feePayment->feeInstallment) {
            $installment = $feePayment->feeInstallment;
            $studentId = (string) $installment->student_id;
            $existing = collect($formData['installmentsByStudent'][$studentId] ?? []);

            if (! $existing->contains('id', $installment->id)) {
                $formData['installmentsByStudent'][$studentId] = $existing->push([
                    'id' => $installment->id,
                    'name' => $installment->title . ' — Due ₹' . number_format($installment->due_amount, 0),
                    'fee_account_id' => $installment->fee_account_id,
                ])->values()->toArray();
            }
        }

        $feePayment->load([
            'branch',
            'student.user',
            'course',
            'batch',
            'feeStructure',
            'feeInstallment',
            'feeAccount',
            'collectedBy',
        ]);

        return view('admin.feePayments.edit', $formData + compact('feePayment'));
    }

    public function update(UpdateFeePaymentRequest $request, FeePayment $feePayment)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

        abort_if($feePayment->payment_status === 'cancelled', Response::HTTP_UNPROCESSABLE_ENTITY, 'A cancelled payment cannot be edited.');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $allocateMultiple = (bool) ($data['allocate_multiple'] ?? false);

        if ($allocateMultiple) {
            abort_if(Gate::denies('fee_payment_allocate'), Response::HTTP_FORBIDDEN, '403 Forbidden');

            $data['fee_installment_id'] = null;
        }

        $data = $this->prepareStudentAndStructureData($data);

        $this->validatePaymentDataAccess($data);

        $data = $this->preparePaymentData($data, $feePayment);

        $previousInstallmentId = $feePayment->fee_installment_id;
        $previousAllocatedInstallmentIds = $feePayment->allocations()->pluck('fee_installment_id')->unique()->all();

        DB::transaction(function () use ($feePayment, $data, $allocateMultiple, $previousInstallmentId, $previousAllocatedInstallmentIds) {
            // Undo whatever this payment previously contributed before re-applying — the simplest
            // correct way to handle an edit that might switch between single/multi allocation, or
            // change amounts enough to change whether an overpayment credit applies. Only the
            // auto-generated "overpayment" credit rows are cleared — a "applied_to_installment"
            // debit represents a distinct real credit-consumption event, not something this
            // payment's own overpayment logic would recreate, so it's left alone.
            $feePayment->allocations()->delete();
            StudentCreditTransaction::where('fee_payment_id', $feePayment->id)->where('source', 'overpayment')->delete();

            $feePayment->update($data);

            if ($allocateMultiple) {
                $this->applyAllocations($feePayment, $data['allocations'] ?? []);
            } elseif ($feePayment->fee_installment_id) {
                $installment = FeeInstallment::find($feePayment->fee_installment_id);

                if ($installment) {
                    $this->recalculateAndCreditExcess($feePayment, $installment);
                }
            }

            $touched = array_unique(array_filter(array_merge(
                [$previousInstallmentId],
                $previousAllocatedInstallmentIds,
                [$feePayment->fee_installment_id]
            )));

            foreach ($touched as $installmentId) {
                FeeInstallment::find($installmentId)?->recalculateFromPayments();
            }

            // Ensure the ledger reflects the deleted overpayment credit even when this edit no
            // longer produces an excess itself (the two methods above only recalculate the ledger
            // when a fresh excess exists).
            $ledger = StudentFeeLedger::where('student_id', $feePayment->student_id)->where('status', 'active')->latest('id')->first();
            $ledger?->recalculate();
        });

        return redirect()->route('admin.fee-payments.index')->with('message', 'Fee payment updated successfully.');
    }

    public function destroy(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

        abort_if(
            (float) $feePayment->paid_amount > 0,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This payment has recorded funds and cannot be deleted — cancel it instead.'
        );

        $installmentId = $feePayment->fee_installment_id;
        $eventEnrollmentId = $feePayment->event_enrollment_id;

        $feePayment->delete();

        if ($installmentId) {
            FeeInstallment::find($installmentId)?->recalculateFromPayments();
        }

        if ($eventEnrollmentId) {
            \App\Models\EventEnrollment::find($eventEnrollmentId)?->recalculateFromPayments();
        }

        return back()->with('message', 'Fee payment deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = FeePayment::whereIn('id', request('ids'))->where('paid_amount', '<=', 0);

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function cancel(CancelFeePaymentRequest $request, FeePayment $feePayment)
    {
        $this->checkFeePaymentAccess($feePayment);

        abort_if($feePayment->payment_status === 'cancelled', Response::HTTP_UNPROCESSABLE_ENTITY, 'This payment is already cancelled.');

        DB::transaction(function () use ($feePayment, $request) {
            $touchedInstallmentIds = array_unique(array_filter(array_merge(
                [$feePayment->fee_installment_id],
                $feePayment->allocations()->pluck('fee_installment_id')->all()
            )));

            $feePayment->update([
                'payment_status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by_id' => auth()->id(),
                'cancel_reason' => $request->validated()['cancel_reason'],
            ]);

            foreach ($touchedInstallmentIds as $installmentId) {
                FeeInstallment::find($installmentId)?->recalculateFromPayments();
            }

            if ($feePayment->event_enrollment_id) {
                \App\Models\EventEnrollment::find($feePayment->event_enrollment_id)?->recalculateFromPayments();
            }

            // Any advance/credit that this payment generated as an overpayment is no longer
            // valid money — reverse it, then let the ledger resum so advance_balance drops back.
            $hadOverpaymentCredit = StudentCreditTransaction::where('fee_payment_id', $feePayment->id)
                ->where('source', 'overpayment')
                ->exists();

            StudentCreditTransaction::where('fee_payment_id', $feePayment->id)->where('source', 'overpayment')->delete();

            if ($hadOverpaymentCredit || empty($touchedInstallmentIds)) {
                $ledger = StudentFeeLedger::where('student_id', $feePayment->student_id)->where('status', 'active')->latest('id')->first();
                $ledger?->recalculate();
            }
        });

        return back()->with('message', 'Fee payment cancelled successfully. Receipt No. ' . $feePayment->receipt_no . ' will not be reused.');
    }

    public function invoice(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

        $feePayment->load([
            'branch',
            'student.user',
            'course',
            'batch',
            'feeStructure',
            'feeAccount',
            'collectedBy',
            'eventEnrollment.event',
            'eventEnrollment.student.user',
            'eventEnrollment.externalContact',
        ]);

        return view('admin.feePayments.invoice', compact('feePayment'));
    }

    private function prepareStudentAndStructureData(array $data): array
    {
        if (! empty($data['student_id'])) {
            $student = Student::find($data['student_id']);

            if ($student) {
                $data['branch_id'] = $data['branch_id'] ?? $student->branch_id;
                $data['course_id'] = $student->course_id ?? $data['course_id'] ?? null;
                $data['batch_id']  = $student->batch_id ?? $data['batch_id'] ?? null;
            }
        }

        if (! empty($data['fee_structure_id'])) {
            $feeStructure = FeeStructure::find($data['fee_structure_id']);

            if ($feeStructure) {
                $data['branch_id'] = $data['branch_id'] ?? $feeStructure->branch_id;
                $data['course_id'] = $data['course_id'] ?? $feeStructure->course_id;
                $data['batch_id']  = $data['batch_id'] ?? $feeStructure->batch_id;

                if (empty($data['total_fee']) || (float) $data['total_fee'] <= 0) {
                    $data['total_fee'] = $feeStructure->total_fee;
                }
            }
        }

        if (! empty($data['fee_installment_id']) && empty($data['fee_account_id'])) {
            $installment = FeeInstallment::find($data['fee_installment_id']);

            if ($installment && $installment->fee_account_id) {
                $data['fee_account_id'] = $installment->fee_account_id;
            }
        }

        return $data;
    }

    private function validatePaymentDataAccess(array $data): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

        if (! empty($data['branch_id'])) {
            abort_if($data['branch_id'] != $branchId, Response::HTTP_FORBIDDEN, 'Invalid branch.');
        }

        if (! empty($data['student_id'])) {
            abort_if(
                ! Student::where('id', $data['student_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid student for your branch.'
            );
        }

        if (! empty($data['course_id'])) {
            abort_if(
                ! Course::where('id', $data['course_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid course for your branch.'
            );
        }

        if (! empty($data['batch_id'])) {
            abort_if(
                ! Batch::where('id', $data['batch_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid batch for your branch.'
            );
        }

        if (! empty($data['fee_structure_id'])) {
            abort_if(
                ! FeeStructure::where('id', $data['fee_structure_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid fee structure for your branch.'
            );
        }

        if (! empty($data['fee_account_id'])) {
            abort_if(
                ! FeeAccount::where('id', $data['fee_account_id'])
                    ->where(fn ($q) => $q->where('branch_id', $branchId)->orWhereNull('branch_id'))
                    ->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid fee account for your branch.'
            );
        }
    }

    private function checkFeePaymentAccess(FeePayment $feePayment): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            abort_if(! $student || $feePayment->student_id != $student->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            abort_if(! $batchIds->contains($feePayment->batch_id), Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $feePayment->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function preparePaymentData(array $data, ?FeePayment $feePayment = null, ?ReceiptNumberService $receiptNumbers = null): array
    {
        $totalFee = (float) ($data['total_fee'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        $payableAmount = max($totalFee - $discount, 0);
        $dueAmount = max($payableAmount - $paidAmount, 0);

        if (empty($data['receipt_no'])) {
            $receiptNumbers = $receiptNumbers ?: app(ReceiptNumberService::class);

            $paymentDate = ! empty($data['payment_date']) ? \Carbon\Carbon::parse($data['payment_date']) : now();
            $academicYear = optional(FeeStructure::find($data['fee_structure_id'] ?? null))->academic_year
                ?? $receiptNumbers->academicYearFor($paymentDate);

            $receipt = $receiptNumbers->next($data['branch_id'] ?? null, $academicYear);

            $data['receipt_no'] = $receipt['receipt_no'];
            $data['receipt_academic_year'] = $receipt['academic_year'];
            $data['receipt_sequence_no'] = $receipt['sequence_no'];
        }

        $data['discount'] = $discount;
        $data['payable_amount'] = $payableAmount;
        $data['due_amount'] = $dueAmount;

        $data['gst_applicable'] = (bool) ($data['gst_applicable'] ?? false);
        $data['gst_percent'] = $data['gst_applicable'] ? (float) ($data['gst_percent'] ?? 0) : 0;
        $data['gst_amount'] = $data['gst_applicable'] ? (float) ($data['gst_amount'] ?? 0) : 0;

        if (($data['payment_status'] ?? null) !== 'cancelled') {
            if ($paidAmount >= $payableAmount && $payableAmount > 0) {
                $data['payment_status'] = 'paid';
            } elseif ($paidAmount > 0) {
                $data['payment_status'] = 'partial';
            } else {
                $data['payment_status'] = 'due';
            }
        }

        if (empty($data['collected_by_id'])) {
            $data['collected_by_id'] = auth()->id();
        }

        if (empty($data['payment_date'])) {
            $data['payment_date'] = now()->format('Y-m-d');
        }

        return $data;
    }

    private function paymentModes(): array
    {
        return [
            'cash' => 'Cash',
            'upi' => 'UPI',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'other' => 'Other',
        ];
    }

    private function formData(): array
    {
        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $students = Student::with('user')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->get()
            ->pluck('user.name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $feeStructures = FeeStructure::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->get()
            ->mapWithKeys(function ($feeStructure) {
                $title = $feeStructure->title ?? 'Fee Structure';
                $total = number_format($feeStructure->total_fee, 0);

                return [$feeStructure->id => $title . ' - ₹' . $total];
            })
            ->prepend(trans('global.pleaseSelect'), '');

        $feeAccounts = FeeAccount::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where(fn ($q) => $q->where('branch_id', $branchId)->orWhereNull('branch_id')) : $query->whereRaw('1 = 0');
            })
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();

        $feeStructureData = FeeStructure::select('id', 'branch_id', 'course_id', 'batch_id', 'total_fee')
            ->where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->get()
            ->keyBy('id');

        return [
            'branches' => $branches,
            'students' => $students,
            'courses' => $courses,
            'batches' => $batches,
            'feeStructures' => $feeStructures,
            'feeAccounts' => $feeAccounts,
            'users' => $users,
            'paymentModes' => $paymentModes,
            'feeStructureData' => $feeStructureData,
            'batchesByBranch' => $this->batchesByBranch(),
            'coursesByBatch' => $this->coursesByBatch(),
            'studentDetails' => $this->studentDetails($branchId),
            'installmentsByStudent' => $this->installmentsByStudent($branchId),
        ];
    }

    /**
     * Branch/course/batch per student, keyed by student id, so the fee payment form can
     * auto-fill the Student Mapping fields (and, in turn, the matching Fee Structure) instead
     * of asking staff to re-select data that already lives on the Student record.
     */
    private function studentDetails(?int $branchId): array
    {
        return Student::with('user')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->get()
            ->mapWithKeys(fn ($student) => [$student->id => [
                'branch_id' => $student->branch_id,
                'course_id' => $student->course_id,
                'batch_id' => $student->batch_id,
            ]])
            ->toArray();
    }

    /**
     * A student's not-yet-fully-paid installments, grouped by student_id, so the fee payment
     * form can cascade its optional "Installment" select the same way it cascades Batch/Course
     * off Branch — pick a student, only that student's outstanding installments show up. Each
     * row also carries its fee_account_id so the form can auto-select the matching Fee Account.
     */
    private function installmentsByStudent(?int $branchId): array
    {
        return FeeInstallment::whereIn('status', ['pending', 'partial'])
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId
                    ? $query->whereHas('student', fn ($q) => $q->where('branch_id', $branchId))
                    : $query->whereRaw('1 = 0');
            })
            ->get(['id', 'student_id', 'title', 'due_amount', 'fee_account_id'])
            ->groupBy('student_id')
            ->map(fn ($installments) => $installments->map(fn ($installment) => [
                'id' => $installment->id,
                'name' => $installment->title . ' — Due ₹' . number_format($installment->due_amount, 0),
                'fee_account_id' => $installment->fee_account_id,
            ])->values())
            ->toArray();
    }

    private function getUserBranchId()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return null;
        }

        $managedBranch = Branch::where('manager_id', $user->id)->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        $staff = Staff::where('user_id', $user->id)->first();

        if ($staff) {
            return $staff->branch_id;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            return $student->branch_id;
        }

        return null;
    }

    private function getTeacherBatchIds()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return collect([]);
        }

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->whereNotNull('batch_id')
            ->pluck('batch_id')
            ->unique()
            ->values();
    }

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }
}
