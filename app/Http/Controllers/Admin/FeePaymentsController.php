<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeePaymentRequest;
use App\Http\Requests\UpdateFeePaymentRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\FeeInstallment;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
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
            'collectedBy',
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

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();

        $feeStructureData = FeeStructure::select('id', 'branch_id', 'course_id', 'batch_id', 'total_fee')
    ->where('status', 'active')
    ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
        $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
    })
    ->get()
    ->keyBy('id');

        $batchesByBranch = $this->batchesByBranch();
        $coursesByBatch = $this->coursesByBatch();
        $studentDetails = $this->studentDetails($branchId);
        $installmentsByStudent = $this->installmentsByStudent($branchId);

        return view('admin.feePayments.create', compact(
            'branches',
            'students',
            'courses',
            'batches',
            'feeStructures',
            'users',
            'paymentModes',
            'feeStructureData',
            'batchesByBranch',
            'coursesByBatch',
            'studentDetails',
            'installmentsByStudent'

        ));
    }

    public function store(StoreFeePaymentRequest $request, WhatsappService $whatsapp)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $data = $this->prepareStudentAndStructureData($data);

        $this->validatePaymentDataAccess($data);

        $data = $this->preparePaymentData($data);

        $feePayment = FeePayment::create($data);

        if ($feePayment->fee_installment_id) {
            FeeInstallment::find($feePayment->fee_installment_id)?->recalculateFromPayments();
        }

        if ($feePayment->student) {
            $whatsapp->sendStudentGuardianMessage(
                $feePayment->student,
                'fee_payment',
                'Fee received. Receipt No: ' . $feePayment->receipt_no . ', Paid: ' . $feePayment->paid_amount . ', Due: ' . $feePayment->due_amount
            );
        }

        return redirect()->route('admin.fee-payments.index')->with('message', 'Fee payment created successfully.');
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
            'collectedBy',
        ]);

        return view('admin.feePayments.show', compact('feePayment'));
    }

    public function edit(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

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

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();
        $batchesByBranch = $this->batchesByBranch();
        $coursesByBatch = $this->coursesByBatch();
        $studentDetails = $this->studentDetails($branchId);
        $installmentsByStudent = $this->installmentsByStudent($branchId);

        // The installment this payment is already linked to may have just become fully
        // paid because of this very payment — still surface it so the edit form doesn't
        // silently lose the current selection.
        if ($feePayment->fee_installment_id && $feePayment->feeInstallment) {
            $installment = $feePayment->feeInstallment;
            $studentId = (string) $installment->student_id;
            $existing = collect($installmentsByStudent[$studentId] ?? []);

            if (! $existing->contains('id', $installment->id)) {
                $installmentsByStudent[$studentId] = $existing->push([
                    'id' => $installment->id,
                    'name' => $installment->title . ' — Due ₹' . number_format($installment->due_amount, 0),
                ])->values()->toArray();
            }
        }

        $feeStructureData = FeeStructure::select('id', 'branch_id', 'course_id', 'batch_id', 'total_fee')
            ->where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->get()
            ->keyBy('id');

        $feePayment->load([
            'branch',
            'student.user',
            'course',
            'batch',
            'feeStructure',
            'feeInstallment',
            'collectedBy',
        ]);

        return view('admin.feePayments.edit', compact(
            'feePayment',
            'branches',
            'students',
            'courses',
            'batches',
            'feeStructures',
            'users',
            'paymentModes',
            'feeStructureData',
            'batchesByBranch',
            'coursesByBatch',
            'installmentsByStudent',
            'studentDetails'
        ));
    }

    public function update(UpdateFeePaymentRequest $request, FeePayment $feePayment)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $data = $this->prepareStudentAndStructureData($data);

        $this->validatePaymentDataAccess($data);

        $data = $this->preparePaymentData($data, $feePayment);

        $previousInstallmentId = $feePayment->fee_installment_id;

        $feePayment->update($data);

        foreach (array_filter(array_unique([$previousInstallmentId, $feePayment->fee_installment_id])) as $installmentId) {
            FeeInstallment::find($installmentId)?->recalculateFromPayments();
        }

        return redirect()->route('admin.fee-payments.index')->with('message', 'Fee payment updated successfully.');
    }

    public function destroy(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkFeePaymentAccess($feePayment);

        $installmentId = $feePayment->fee_installment_id;

        $feePayment->delete();

        if ($installmentId) {
            FeeInstallment::find($installmentId)?->recalculateFromPayments();
        }

        return back()->with('message', 'Fee payment deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = FeePayment::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
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
            'collectedBy',
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

    private function preparePaymentData(array $data, FeePayment $feePayment = null): array
    {
        $totalFee = (float) ($data['total_fee'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        $payableAmount = max($totalFee - $discount, 0);
        $dueAmount = max($payableAmount - $paidAmount, 0);

        if (empty($data['receipt_no'])) {
            $data['receipt_no'] = $feePayment->receipt_no ?? $this->generateReceiptNo();
        }

        $data['discount'] = $discount;
        $data['payable_amount'] = $payableAmount;
        $data['due_amount'] = $dueAmount;

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

    private function generateReceiptNo(): string
    {
        $lastPayment = FeePayment::latest('id')->first();
        $nextId = $lastPayment ? $lastPayment->id + 1 : 1;

        return 'REC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
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
     * off Branch — pick a student, only that student's outstanding installments show up.
     */
    private function installmentsByStudent(?int $branchId): array
    {
        return FeeInstallment::whereIn('status', ['pending', 'partial'])
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId
                    ? $query->whereHas('student', fn ($q) => $q->where('branch_id', $branchId))
                    : $query->whereRaw('1 = 0');
            })
            ->get(['id', 'student_id', 'title', 'due_amount'])
            ->groupBy('student_id')
            ->map(fn ($installments) => $installments->map(fn ($installment) => [
                'id' => $installment->id,
                'name' => $installment->title . ' — Due ₹' . number_format($installment->due_amount, 0),
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
